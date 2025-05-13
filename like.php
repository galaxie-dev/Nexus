<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $response['message'] = 'Invalid CSRF token';
    error_log('Like failed: Invalid CSRF token');
    echo json_encode($response);
    exit;
}

if (!isLoggedIn() || (isset($_SESSION['anonymous']) && $_SESSION['anonymous'])) {
    $response['message'] = 'Please log in to like posts';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id'])) {
    $news_id = filter_var($_POST['news_id'], FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$news_id) {
        $response['message'] = 'Invalid news ID';
        error_log('Like failed: Invalid news ID');
        echo json_encode($response);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if user already liked
        $stmt = $pdo->prepare("SELECT clicks FROM user_behavior WHERE user_id = ? AND news_id = ?");
        $stmt->execute([$user_id, $news_id]);
        $result = $stmt->fetch();
        $liked = $result && $result['clicks'] > 0;

        if ($liked) {
            // Unlike
            $stmt = $pdo->prepare("UPDATE news_card SET likes = likes - 1, updated_at = NOW() WHERE id = ? AND likes > 0");
            $stmt->execute([$news_id]);
            $stmt = $pdo->prepare("UPDATE user_behavior SET clicks = 0 WHERE user_id = ? AND news_id = ?");
            $stmt->execute([$user_id, $news_id]);
            $response['action'] = 'unliked';
        } else {
            // Like
            $stmt = $pdo->prepare("UPDATE news_card SET likes = likes + 1, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$news_id]);
            $stmt = $pdo->prepare("INSERT INTO user_behavior (user_id, news_id, clicks) 
                                  VALUES (?, ?, 1) 
                                  ON DUPLICATE KEY UPDATE clicks = 1");
            $stmt->execute([$user_id, $news_id]);
            $response['action'] = 'liked';
        }

        // Get updated likes count
        $stmt = $pdo->prepare("SELECT likes FROM news_card WHERE id = ?");
        $stmt->execute([$news_id]);
        $likes = $stmt->fetchColumn();

        $pdo->commit();
        $response['success'] = true;
        $response['likes'] = $likes;
        $response['news_id'] = $news_id;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error';
        error_log('Like failed: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request';
    error_log('Like failed: Invalid request');
}

echo json_encode($response);
?>