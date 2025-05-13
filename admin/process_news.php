<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');
file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Process news request received\n", FILE_APPEND);

requireAdmin();

$response = ['success' => false, 'message' => ''];

// CSRF token validation for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    $response['message'] = 'Invalid CSRF token';
    file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Process news failed: Invalid CSRF token\n", FILE_APPEND);
    echo json_encode($response);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'create';

if ($action === 'create') {
    // Validate inputs
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? '';
    $valid_categories = ['technology', 'sports', 'politics', 'entertainment'];

    if (empty($title) || strlen($title) > 100) {
        $response['message'] = 'Title is required and must be 100 characters or less';
        echo json_encode($response);
        exit;
    }

    if (empty($content) || strlen($content) > 1000) {
        $response['message'] = 'Content is required and must be 1000 characters or less';
        echo json_encode($response);
        exit;
    }

    if (!in_array($category, $valid_categories)) {
        $response['message'] = 'Invalid category';
        echo json_encode($response);
        exit;
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $valid_types = ['image/jpeg', 'image/png'];
        if (!in_array($image['type'], $valid_types)) {
            $response['message'] = 'Image must be JPEG or PNG';
            echo json_encode($response);
            exit;
        }
        if ($image['size'] > 5 * 1024 * 1024) {
            $response['message'] = 'Image must be less than 5MB';
            echo json_encode($response);
            exit;
        }
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = 'news_' . time() . '_' . uniqid() . '.' . $ext;
        $upload_dir = __DIR__ . '/../uploads/';
        $image_path = 'uploads/' . $filename;
        if (!move_uploaded_file($image['tmp_name'], $upload_dir . $filename)) {
            $response['message'] = 'Failed to upload image';
            file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Process news failed: Image upload error\n", FILE_APPEND);
            echo json_encode($response);
            exit;
        }
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO news_card (title, content, category, image_path, likes, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, 0, NOW(), NOW())");
        $stmt->execute([$title, $content, $category, $image_path]);
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'News uploaded successfully';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error';
        file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Process news failed: " . $e->getMessage() . "\n", FILE_APPEND);
    }
} elseif ($action === 'edit') {
    // Validate inputs
    $news_id = filter_var($_POST['news_id'] ?? 0, FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? '';
    $valid_categories = ['technology', 'sports', 'politics', 'entertainment'];

    if (!$news_id) {
        $response['message'] = 'Invalid news ID';
        echo json_encode($response);
        exit;
    }

    if (empty($title) || strlen($title) > 100) {
        $response['message'] = 'Title is required and must be 100 characters or less';
        echo json_encode($response);
        exit;
    }

    if (empty($content) || strlen($content) > 1000) {
        $response['message'] = 'Content is required and must be 1000 characters or less';
        echo json_encode($response);
        exit;
    }

    if (!in_array($category, $valid_categories)) {
        $response['message'] = 'Invalid category';
        echo json_encode($response);
        exit;
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $valid_types = ['image/jpeg', 'image/png'];
        if (!in_array($image['type'], $valid_types)) {
            $response['message'] = 'Image must be JPEG or PNG';
            echo json_encode($response);
            exit;
        }
        if ($image['size'] > 5 * 1024 * 1024) {
            $response['message'] = 'Image must be less than 5MB';
            echo json_encode($response);
            exit;
        }
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = 'news_' . time() . '_' . uniqid() . '.' . $ext;
        $upload_dir = __DIR__ . '/../uploads/';
        $image_path = 'uploads/' . $filename;
        if (!move_uploaded_file($image['tmp_name'], $upload_dir . $filename)) {
            $response['message'] = 'Failed to upload image';
            file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Process news failed: Image upload error\n", FILE_APPEND);
            echo json_encode($response);
            exit;
        }
    }

    try {
        $pdo->beginTransaction();
        if ($image_path) {
            // Delete old image if exists
            $stmt = $pdo->prepare("SELECT image_path FROM news_card WHERE id = ?");
            $stmt->execute([$news_id]);
            $old_image = $stmt->fetchColumn();
            if ($old_image && file_exists(__DIR__ . '/../' . $old_image)) {
                unlink(__DIR__ . '/../' . $old_image);
            }
            $stmt = $pdo->prepare("UPDATE news_card SET title = ?, content = ?, category = ?, image_path = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $content, $category, $image_path, $news_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE news_card SET title = ?, content = ?, category = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $content, $category, $news_id]);
        }
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'News updated successfully';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error';
        file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Process news failed: " . $e->getMessage() . "\n", FILE_APPEND);
    }
} elseif ($action === 'delete') {
    $news_id = filter_var($_POST['news_id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$news_id) {
        $response['message'] = 'Invalid news ID';
        echo json_encode($response);
        exit;
    }

    try {
        $pdo->beginTransaction();
        // Delete image if exists
        $stmt = $pdo->prepare("SELECT image_path FROM news_card WHERE id = ?");
        $stmt->execute([$news_id]);
        $image_path = $stmt->fetchColumn();
        if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
            unlink(__DIR__ . '/../' . $image_path);
        }
        // Delete news
        $stmt = $pdo->prepare("DELETE FROM news_card WHERE id = ?");
        $stmt->execute([$news_id]);
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'News deleted successfully';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error';
        file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Process news failed: " . $e->getMessage() . "\n", FILE_APPEND);
    }
} elseif ($action === 'get') {
    $news_id = filter_var($_GET['news_id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$news_id) {
        $response['message'] = 'Invalid news ID';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, title, content, category, image_path FROM news_card WHERE id = ?");
        $stmt->execute([$news_id]);
        $news = $stmt->fetch();
        if ($news) {
            $response['success'] = true;
            $response['news'] = [
                'id' => $news['id'],
                'title' => $news['title'],
                'content' => $news['content'],
                'category' => $news['category'],
                'image_path' => $news['image_path']
            ];
        } else {
            $response['message'] = 'News not found';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error';
        file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Process news failed: " . $e->getMessage() . "\n", FILE_APPEND);
    }
} else {
    $response['message'] = 'Invalid action';
}

echo json_encode($response);
?>