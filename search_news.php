
<?php
header('Content-Type: application/json');
require_once 'includes/db.php';

$query = $_GET['query'] ?? '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Prepare the search query (using LIKE for simple search)
$searchQuery = "%$query%";
$stmt = $conn->prepare("SELECT id, title, content, category, likes FROM news_card 
                        WHERE title LIKE ? OR content LIKE ? 
                        ORDER BY created_at DESC");
$stmt->bind_param("ss", $searchQuery, $searchQuery);
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