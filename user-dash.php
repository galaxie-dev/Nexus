<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Fetch user profile (email and username)
try {
    $user_stmt = $pdo->prepare("SELECT email, username FROM users WHERE id = :user_id");
    $user_stmt->execute(['user_id' => $user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $user_error = "Failed to load user profile.";
    file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - User fetch failed: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Fetch bookmarked news
try {
    $stmt = $pdo->prepare("SELECT n.id, n.title, n.content, n.category, n.image_path, n.likes, n.created_at
                           FROM bookmarks b
                           JOIN news_card n ON b.news_id = n.id
                           WHERE b.user_id = :user_id
                           ORDER BY b.created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $bookmarked_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load bookmarks.";
    file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - Bookmarks fetch failed: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nexus - Profile</title>
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/onnx@0.0.7/dist/onnx.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/idb@7/build/umd.js"></script>

    <style>
        .user-profile {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.user-profile p {
    margin: 5px 0;
}

.user-profile strong {
    color: #333;
}
        </style>
</head>
<body>
    <div class="container" role="main">
        <!-- Fixed Sidebar -->
        <nav class="fixed-sidebar" aria-label="Primary Navigation">
            <button class="close-btn" aria-label="Close menu"><i>NEXUS</i></button>
            <ul>
                <li class="home"><a style="text-decoration: none;" href="index.php"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                <li><a href="bookmarks.php" style="text-decoration: none;"><i class="fas fa-bookmark" aria-hidden="true"></i> Bookmarks</a></li>
                <li><a href="user-dash.php" style="text-decoration: none;"><i class="fas fa-user" aria-hidden="true"></i> Profile</a></li>
            </ul>
        </nav>
        <!-- Main Content -->
        <section>
            <h2>Profile</h2>

            <?php if (isset($user_error)): ?>
                <p class="error"><?php echo htmlspecialchars($user_error); ?></p>
            <?php elseif ($user): ?>
                <div class="user-profile">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                   
                </div>
            <?php else: ?>
                <p>User profile not found.</p>
            <?php endif; ?>   
            
             <p><strong>Liked News</strong> <?php echo htmlspecialchars($user['']); ?></p>
       
        </section>
    </div>
    <script src="assets/js/app.js"></script>
    <script>
         
    </script>
</body>
</html>