<?php

require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Fetch cached news or query database
$cache_key = 'news_public_cache';
$cache_ttl = 300; // 5 minutes
$news_items = [];

if (isset($_SESSION[$cache_key])) {
    $news_items = $_SESSION[$cache_key]['data'];
    // Check if cache is expired
    if ((time() - $_SESSION[$cache_key]['timestamp']) >= $cache_ttl) {
        unset($_SESSION[$cache_key]); // Clear expired cache
    }
}

if (!isset($_SESSION[$cache_key])) {
    try {
        // Fetch news items with bookmark status for current user
        $stmt = $pdo->prepare("
            SELECT n.id, n.title, n.content, n.category, n.image_path, n.likes, n.created_at,
                   (SELECT COUNT(*) FROM bookmarks b WHERE b.news_id = n.id AND b.user_id = ?) as is_bookmarked
            FROM news_card n
            ORDER BY n.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $news_items = $stmt->fetchAll();
        
        $_SESSION[$cache_key] = [
            'data' => $news_items,
            'timestamp' => time()
        ];
    } catch (PDOException $e) {
        $news_items = [];
        $error = "Failed to load news: " . $e->getMessage();
    }
}

// Handle bookmark actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id'])) {
    $news_id = $_POST['news_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'bookmark') {
            // Check if already bookmarked
            $check = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND news_id = ?");
            $check->execute([$user_id, $news_id]);
            
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO bookmarks (user_id, news_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $news_id]);
            }
        } elseif ($action === 'unbookmark') {
            $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND news_id = ?");
            $stmt->execute([$user_id, $news_id]);
        }
        
        // Update the cached data
        foreach ($news_items as &$item) {
            if ($item['id'] == $news_id) {
                $item['is_bookmarked'] = ($action === 'bookmark') ? 1 : 0;
                break;
            }
        }
        $_SESSION[$cache_key]['data'] = $news_items;
        
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Nexus | Home</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="style.css" rel="stylesheet">
     <link href="mobile-style.css" rel="stylesheet">
    <style>

    </style>
</head>
<body>
    <div class="container" role="main">
           
          <?php include 'navigation.php'; ?>

        <!-- Main Content -->
        <main>
            <div class="page-header">NEXUS</div>
            
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (empty($news_items)): ?>
                <p>No news items available.</p>
            <?php else: ?>


                <?php foreach ($news_items as $news): ?>
                <article class="tweet" aria-label="News item: <?php echo htmlspecialchars($news['title']); ?>" 
                onclick="window.location.href='view-article.php?id=<?php echo $news['id']; ?>'">
                    
                     <div class="tweet-header">
                            <div>
                                <span class="name">Nexus News™</span>
                                <span class="category"><?php echo htmlspecialchars(ucfirst($news['category'])); ?></span>                              
                            </div>
                    </div>
                   
                            <?php if ($news['image_path']): ?>
                                <img
                                    src="<?php echo htmlspecialchars($news['image_path']); ?>"
                                    alt="Image for news: <?php echo htmlspecialchars($news['title']); ?>"
                                    class="tweet-image"
                                    loading="lazy"
                                />
                            <?php endif; ?>                           
                            <div class="tweet-header">
                                <span class="time">@nexus · <?php echo date('M j, Y', strtotime($news['created_at'])); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                             <div class="tweet-text"><?php echo htmlspecialchars(substr($news['content'], 0, 250)); ?>
                             <span class="category">...read article</span></div>
                            <footer>
                                <div><i class="far fa-comment"></i> 0</div>
                                <div><i class="far fa-heart"></i> <?php echo $news['likes']; ?></div>
                                <div class="<?php echo $news['is_bookmarked'] ? 'bookmarked' : ''; ?>"
                                     onclick="toggleBookmark(<?php echo $news['id']; ?>, <?php echo $news['is_bookmarked'] ? 'true' : 'false'; ?>, this)">
                                    <i class="fas fa-bookmark"></i>
                                </div>
                                <div><i class="fas fa-share"></i></div>
                            </footer>
                        
                    </article>


                <?php endforeach; ?>
            <?php endif; ?>
        </main>
        
    <?php include 'right-side-bar.php'; ?>
    </div>
    
    <?php include 'mobile-menu.php'; ?>
    
    <script>
        function toggleBookmark(newsId, isBookmarked, element) {
            const action = isBookmarked ? 'unbookmark' : 'bookmark';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `news_id=${newsId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.classList.toggle('bookmarked');
                }
            });
        }
    </script>
      <script src="main.js"></script>
</body>
</html>