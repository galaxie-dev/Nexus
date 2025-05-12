<?php
require 'includes/db.php';
require 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  $postId = $data['postId'];
  
  // Update likes
  $stmt = $conn->prepare("UPDATE news SET likes = likes + 1 WHERE id = ?");
  $stmt->execute([$postId]);
  
  // Get new count
  $stmt = $conn->prepare("SELECT likes FROM news WHERE id = ?");
  $stmt->execute([$postId]);
  $likes = $stmt->fetchColumn();
  
  header('Content-Type: application/json');
  echo json_encode(['success' => true, 'count' => $likes]);
}
