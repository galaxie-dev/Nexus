<?php 
require 'includes/auth.php';
if (!isLoggedIn()) header("Location: login.php");
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nexus</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen">
  <!-- Header -->
  <header class="sticky top-0 bg-gray-800 p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-white">Nexus</h1>
    <div class="flex space-x-4">
      <button id="anonymousToggle" class="bg-purple-600 px-3 py-1 rounded-full text-sm">
        Anonymous Mode
      </button>
      <a href="includes/auth.php?logout" class="text-white hover:text-purple-400">Logout</a>
    </div>
  </header>

  <!-- Feed -->
  <div id="newsFeed" class="max-w-2xl mx-auto py-4 divide-y divide-gray-700">
    <?php
    require 'includes/db.php';
    $stmt = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
    while ($row = $stmt->fetch()):
    ?>
    <div class="p-4" data-news-id="<?= $row['id'] ?>">
      <h2 class="text-xl font-semibold text-white"><?= htmlspecialchars($row['title']) ?></h2>
      <p class="text-gray-400 mt-2"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
      
      <!-- Like Button -->
      <div class="flex items-center mt-4">
        <button onclick="likePost(<?= $row['id'] ?>)" 
                class="flex items-center text-gray-400 hover:text-red-500">
          ♥ <span id="likes-<?= $row['id'] ?>" class="ml-1"><?= $row['likes'] ?></span>
        </button>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <script>
  // Real-time likes
  function likePost(postId) {
    fetch('like.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ postId })
    });
  }

  // SSE for real-time updates
  const eventSource = new EventSource('includes/sse.php');
  eventSource.onmessage = (e) => {
    const data = JSON.parse(e.data);
    if (data.type === 'like') {
      document.getElementById(`likes-${data.postId}`).textContent = data.count;
    }
  };
  </script>
</body>
</html>
