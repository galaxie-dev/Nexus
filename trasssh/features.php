<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();
header('Content-Type: application/json');
try {
    $stmt = $pdo->prepare("SELECT AVG(scroll_depth) as avg_scroll, AVG(dwell_time) as avg_dwell 
                           FROM user_behavior 
                           WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $features = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($features ?: ['avg_scroll' => 0, 'avg_dwell' => 0]);
} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - Features failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>