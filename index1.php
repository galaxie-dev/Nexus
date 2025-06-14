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
                    (SELECT COUNT(*) FROM bookmarks b WHERE b.news_id = n.id AND b.user_id = ?) as is_bookmarked,
                    (SELECT COUNT(*) FROM likes WHERE news_id = n.id AND user_id = ?) as is_liked,
                    (SELECT COUNT(*) FROM comments WHERE news_id = n.id) as comment_count
                FROM news_card n
                ORDER BY n.created_at DESC
            ");
            $stmt->execute([$user_id, $user_id]);
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
            .notification {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #1da1f2;
        color: white;
        padding: 12px 24px;
        border-radius: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        display: flex;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s, transform 0.3s;
    }
    
    .notification.show {
        opacity: 1;
        transform: translateX(-50%) translateY(-10px);
    }
    
    .notification i {
        margin-right: 8px;
    }
    
    @keyframes firework {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    
    .firework {
        position: absolute;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background-color: #ff0;
        animation: firework 0.5s ease-out forwards;
    }

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
                <!-- <article class="tweet" aria-label="News item: <?php echo htmlspecialchars($news['title']); ?>" 
                onclick="window.location.href='view-article.php?id=<?php echo $news['id']; ?>'"> -->

                <article class="tweet" data-id="<?php echo $news['id']; ?>" aria-label="News item: 
                <?php echo htmlspecialchars($news['title']); ?>">
                    
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
                                <span class="time">@nexus · <?php echo date('M j, Y g:i a', strtotime($news['created_at'])); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                             <div class="tweet-text"><?php echo htmlspecialchars(substr($news['content'], 0, 250)); ?>
                             <span class="category">...read article</span></div>
                           <!-- Update the footer in index1.php -->
                            <footer>
                                <div class="comment-button" onclick="window.location.href='view-article.php?id=<?php echo $news['id']; ?>'">
                                    <i class="far fa-comment"></i> <?php echo $news['comment_count']; ?>
                                </div>
                                <div class="like-button" onclick="toggleLike(<?php echo $news['id']; ?>, <?php echo $news['is_liked'] ? 'true' : 'false'; ?>, this, event)">
                                    <i class="<?php echo $news['is_liked'] ? 'fas liked' : 'far'; ?> fa-heart"></i> 
                                    <span class="like-count"><?php echo $news['likes']; ?></span>
                                </div>
                                <div class="bookmark-button <?php echo $news['is_bookmarked'] ? 'bookmarked' : ''; ?>"
                                    onclick="toggleBookmark(<?php echo $news['id']; ?>, <?php echo $news['is_bookmarked'] ? 'true' : 'false'; ?>, this, event)">
                                    <i class="fas fa-bookmark"></i>
                                </div>
                                <div class="share-button" onclick="shareArticle(<?php echo $news['id']; ?>, event)">
                                    <i class="fas fa-share"></i>
                                </div>
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






        // notification anime
        function showNotification(message) {
        const notification = document.getElementById('notification');
        const text = document.getElementById('notification-text');
        
        text.textContent = message;
        notification.style.display = 'flex';
        
        // Trigger reflow
        void notification.offsetWidth;
        
        notification.classList.add('show');
        
        // Create fireworks
        for (let i = 0; i < 10; i++) {
            createFirework(notification);
        }
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 2000);
    }
    
    function createFirework(container) {
        const firework = document.createElement('div');
        firework.className = 'firework';
        
        // Random position around the notification
        const angle = Math.random() * Math.PI * 2;
        const distance = 20 + Math.random() * 30;
        const x = Math.cos(angle) * distance;
        const y = Math.sin(angle) * distance;
        
        firework.style.left = `calc(50% + ${x}px)`;
        firework.style.top = `calc(50% + ${y}px)`;
        firework.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
        
        container.appendChild(firework);
        
        // Remove after animation
        setTimeout(() => {
            firework.remove();
        }, 500);
    }
    
    function toggleLike(newsId, isLiked, element, event) {
        event.stopPropagation();
        event.preventDefault();
        const action = isLiked ? 'unlike' : 'like';
        
        fetch('view-article.php?id=' + newsId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update heart icon
                const heartIcon = element.querySelector('i');
                if (data.is_liked) {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas', 'liked');
                    showNotification('You liked this article');
                } else {
                    heartIcon.classList.remove('fas', 'liked');
                    heartIcon.classList.add('far');
                    showNotification('You unliked this article');
                }
                
                // Update like count
                const likeCount = element.querySelector('.like-count');
                if (likeCount) {
                    likeCount.textContent = data.likes;
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    function toggleBookmark(newsId, isBookmarked, element, event) {
        event.stopPropagation();
        event.preventDefault();
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
                showNotification(isBookmarked ? 'Removed from bookmarks' : 'Article bookmarked');
            }
        });
    }







// Handle article clicks
document.querySelectorAll('.tweet').forEach(article => {
    article.addEventListener('click', function(e) {
        // Check if the click was on an interactive element or its children
        const interactiveElements = ['I', 'BUTTON', 'A', 'INPUT'];
        const clickedElement = e.target;
        
        // Don't navigate if clicking on interactive elements or footer
        // if (interactiveElements.includes(clickedElement.tagName)) {
        //     return;
        // }
        
        // Don't navigate if clicking on a child of an interactive element
        if (clickedElement.closest('footer') || 
            clickedElement.closest('.fa-heart') || 
            clickedElement.closest('.fa-bookmark') || 
            clickedElement.closest('.fa-share')) {
            return;
        }
        
        // Otherwise, navigate to the article
        window.location.href = 'view-article.php?id=' + article.dataset.id;
    });
});



// share handling
function shareArticle(newsId, event) {
    event.stopPropagation();
    event.preventDefault();
    // share functionality
    console.log('Sharing article', newsId);
}
    </script>
      <script src="main.js"></script>



      <div id="notification" class="notification" style="display: none;">
    <i class="fas fa-check"></i>
    <span id="notification-text"></span>
</div>
</body>
</html>