<?php
// get_suggested_news.php
header('Content-Type: application/json');
require_once 'includes/db.php';

// Get trending news based on likes + comments
$query = "SELECT n.id, n.title, n.content, n.category, n.likes, 
          COUNT(c.id) as comments_count
          FROM news_card n
          LEFT JOIN comments c ON n.id = c.news_id
          GROUP BY n.id
          ORDER BY (n.likes + COUNT(c.id)) DESC, n.created_at DESC
          LIMIT 3";

$result = $conn->query($query);

$news = [];
while ($row = $result->fetch_assoc()) {
    $news[] = $row;
}

echo json_encode($news);
$conn->close();
?>