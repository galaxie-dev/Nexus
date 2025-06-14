<?php
// search_news.php
header('Content-Type: application/json');
require_once 'includes/db.php';

$query = $_GET['query'] ?? '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Prepare the search query with better ranking
$searchQuery = "%$query%";
$stmt = $conn->prepare("SELECT id, title, content, category, likes, 
                       (LENGTH(title) - LENGTH(REPLACE(LOWER(title), LOWER(?), '')) * 10 +
                       (LENGTH(content) - LENGTH(REPLACE(LOWER(content), LOWER(?), '')) as relevance
                       FROM news_card 
                       WHERE title LIKE ? OR content LIKE ? 
                       ORDER BY relevance DESC, likes DESC
                       LIMIT 10");
$stmt->bind_param("ssss", $query, $query, $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

$news = [];
while ($row = $result->fetch_assoc()) {
    $news[] = $row;
}

echo json_encode($news);
$stmt->close();
$conn->close();
?>