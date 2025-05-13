<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isLoggedIn() && !(isset($_SESSION['anonymous']) && $_SESSION['anonymous'])) {
    $response['message'] = 'Please log in to share';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id'], $_POST['platform'])) {
    $news_id = filter_var($_POST['news_id'], FILTER_VALIDATE_INT);
    $platform = in_array($_POST['platform'], ['whatsapp', 'twitter']) ? $_POST['platform'] : null;
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;

    if (!$news_id || !$platform) {
        $response['message'] = 'Invalid news ID or platform';
        echo json_encode($response);
        exit;
    }

    try {
        // Log share action (placeholder table for future analytics)
        $stmt = $pdo->prepare("INSERT INTO user_behavior (user_id, news_id, clicks) 
                              VALUES (?, ?, 1) 
                              ON DUPLICATE KEY UPDATE clicks = clicks + 1");
        $stmt->execute([$user_id ?? 0, $news_id]);
        $response['success'] = true;
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
?>