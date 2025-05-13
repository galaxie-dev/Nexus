<?php
header('Content-Type: application/json');
// Note: ONNX Runtime PHP is limited; mood prediction is handled client-side in index_after.php
echo json_encode(['message' => 'Mood prediction handled client-side']);
?>