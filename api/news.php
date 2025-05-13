<?php
require_once '../includes/db.php';
try {
    $stmt = $pdo->query("SELECT id, title, content, category, image_path, likes, created_at FROM news_card");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($news);
} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - News API failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch news']);
}
?>