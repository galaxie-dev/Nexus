<?php
header('Content-Type: application/json');
require_once 'includes/db.php';

// In a real application, you would use the logged-in user's ID to personalize suggestions
// $user_id = $_SESSION['user_id'] ?? 0;

// For now, we'll just get trending news (most liked)
$query = "SELECT id, title, content, category, likes FROM news_card 
          ORDER BY likes DESC, created_at DESC LIMIT 3";
$result = $conn->query($query);

$news = [];
while ($row = $result->fetch_assoc()) {
    $news[] = $row;
}

echo json_encode($news);
$conn->close();
?>