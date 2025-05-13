<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$response = [
    'session_active' => session_status() === PHP_SESSION_ACTIVE,
    'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
    'csrf_token' => isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : null,
    'anonymous_mode' => isset($_SESSION['anonymous']) && $_SESSION['anonymous'],
    'database_connection' => false,
    'file_paths' => [],
    'recent_logs' => []
];

// Test database connection
try {
    $pdo->query("SELECT 1");
    $response['database_connection'] = true;
} catch (PDOException $e) {
    $response['database_error'] = $e->getMessage();
}

// Check file paths
$files = ['like.php', 'comment.php', 'includes/sse.php', 'assets/js/app.js', 'style.css'];
foreach ($files as $file) {
    $response['file_paths'][$file] = file_exists(__DIR__ . '/' . $file) ? 'exists' : 'missing';
}

// Read recent logs
$log_file = __DIR__ . '/logs/nexus.log';
if (file_exists($log_file)) {
    $logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $response['recent_logs'] = array_slice($logs, -10); // Last 10 lines
}

echo json_encode($response);
?>