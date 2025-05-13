<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $response['message'] = 'Invalid CSRF token';
    error_log('Comment failed: Invalid CSRF token');
    echo json_encode($response);
    exit;
}

if (!isLoggedIn() || (isset($_SESSION['anonymous']) && $_SESSION['anonymous'])) {
    $response['message'] = 'Please log in to comment';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id'], $_POST['content'])) {
    $news_id = filter_var($_POST['news_id'], FILTER_VALIDATE_INT);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if (!$news_id || empty($content) || strlen($content) > 280) {
        $response['message'] = 'Invalid news ID or comment (max 280 characters)';
        error_log('Comment failed: Invalid input');
        echo json_encode($response);
        exit;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO comments (news_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$news_id, $user_id, $content]);

        // Update news_card updated_at
        $stmt = $pdo->prepare("UPDATE news_card SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$news_id]);

        // Get comment count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE news_id = ?");
        $stmt->execute([$news_id]);
        $comment_count = $stmt->fetchColumn();

        $pdo->commit();

        $response['success'] = true;
        $response['comment_count'] = $comment_count;
        $response['news_id'] = $news_id;
        $response['content'] = htmlspecialchars($content);
        $response['username'] = htmlspecialchars($_SESSION['username']);
        $response['created_at'] = date('M j, Y H:i');
        $response['comment_id'] = $pdo->lastInsertId();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error';
        error_log('Comment failed: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request';
    error_log('Comment failed: Invalid request');
}

echo json_encode($response);
?>