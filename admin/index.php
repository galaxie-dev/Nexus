<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

// Generate CSRF token (for consistency)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nexus - Admin Dashboard</title>
    <link href="../style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
</head>
<body>
    <div class="container" role="main">
        <!-- Fixed Sidebar -->
        <nav class="fixed-sidebar" aria-label="Primary Navigation">
            <button class="close-btn" aria-label="Close menu"><i>NEXUS</i></button>
            <ul>
                <li class="home"><a href="../index_after.php"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                <li><a href="index.php"><i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard</a></li>
                <li><a href="upload_news.php"><i class="fas fa-plus" aria-hidden="true"></i> Upload News</a></li>
                <li><a href="manage_news.php"><i class="fas fa-list" aria-hidden="true"></i> Manage News</a></li>
                <li><a href="../includes/logout.php" class="post-btn" aria-label="Log out">Log Out</a></li>
            </ul>
        </nav>
        <!-- Main Content -->
        <main class="main-content">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! Manage Nexus news content below.</p>
            <div class="admin-links">
                <a href="upload_news.php" class="admin-link">
                    <i class="fas fa-plus"></i>
                    <h2>Upload News</h2>
                    <p>Create a new news article with rich text and images.</p>
                </a>
                <a href="manage_news.php" class="admin-link">
                    <i class="fas fa-list"></i>
                    <h2>Manage News</h2>
                    <p>View, edit, or delete existing news articles.</p>
                </a>
            </div>
        </main>
    </div>
</body>
</html>