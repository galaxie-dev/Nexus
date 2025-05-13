<?php
require_once 'includes/db.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['user_id'], $data['news_id'], $data['scroll_depth'], $data['dwell_time'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}
try {
    $stmt = $pdo->prepare("INSERT INTO user_behavior (user_id, news_id, scroll_depth, dwell_time, timestamp) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$data['user_id'], $data['news_id'], $data['scroll_depth'], $data['dwell_time']]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - Track failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>