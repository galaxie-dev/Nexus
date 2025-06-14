<?php
require_once 'includes/db.php';
header('Content-Type: application/json');
$mood = $_GET['mood'] ?? 'positive';
$categories = $mood === 'positive' ? ['entertainment', 'lifestyle', 'travel'] : ['politics', 'crime'];
try {
    $stmt = $pdo->prepare("SELECT id, title, content, category, image_path, likes, created_at 
                           FROM news_card 
                           WHERE category IN (".implode(',', array_fill(0, count($categories), '?')).") 
                           ORDER BY created_at DESC 
                           LIMIT 10");
    $stmt->execute($categories);
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($news);
} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - Adjust mood failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>