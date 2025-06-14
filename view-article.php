<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get the article ID from the URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id === 0) {
    header("Location: index1.php");
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    
    if (!empty($comment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (user_id, news_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $article_id, $comment]);
            
            // Refresh comments after adding new one
            $stmt = $pdo->prepare("
                SELECT c.id, c.content, c.created_at, u.username, u.profile_pic
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.news_id = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$article_id]);
            $comments = $stmt->fetchAll();
            
            // Redirect to prevent form resubmission
            header("Location: view-article.php?id=" . $article_id);
            exit;
        } catch (PDOException $e) {
            $error = "Failed to post comment: " . $e->getMessage();
        }
    }
}


// Then fetch article and comments
try {
    // Fetch article details
    $stmt = $pdo->prepare("
        SELECT n.id, n.title, n.content, n.category, n.image_path, n.likes, n.created_at,
               (SELECT COUNT(*) FROM bookmarks b WHERE b.news_id = n.id AND b.user_id = ?) as is_bookmarked,
               (SELECT COUNT(*) FROM likes WHERE news_id = n.id AND user_id = ?) as is_liked
        FROM news_card n
        WHERE n.id = ?
    ");
    $stmt->execute([$user_id, $user_id, $article_id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header("Location: index1.php");
        exit;
    }

    // Fetch comments
    $stmt = $pdo->prepare("
        SELECT c.id, c.content, c.created_at, u.username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.news_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$article_id]);
    $comments = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Failed to load article: " . $e->getMessage());
}





// Handle like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        if ($action === 'like') {
            // Check if already liked
            $check = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND news_id = ?");
            $check->execute([$user_id, $article_id]);
            
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO likes (user_id, news_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $article_id]);
                
                // Update like count (ensure it doesn't go negative)
                $stmt = $pdo->prepare("UPDATE news_card SET likes = GREATEST(0, likes + 1) WHERE id = ?");
                $stmt->execute([$article_id]);
                
                // Get updated like count
                $stmt = $pdo->prepare("SELECT likes FROM news_card WHERE id = ?");
                $stmt->execute([$article_id]);
                $updated = $stmt->fetch();
                
                echo json_encode([
                    'success' => true, 
                    'likes' => $updated['likes'], 
                    'is_liked' => true
                ]);
                exit;
            }
        } elseif ($action === 'unlike') {
            // Check if actually liked before unliking
            $check = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND news_id = ?");
            $check->execute([$user_id, $article_id]);
            
            if ($check->fetch()) {
                $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND news_id = ?");
                $stmt->execute([$user_id, $article_id]);
                
                // Update like count (ensure it doesn't go negative)
                $stmt = $pdo->prepare("UPDATE news_card SET likes = GREATEST(0, likes - 1) WHERE id = ?");
                $stmt->execute([$article_id]);
                
                // Get updated like count
                $stmt = $pdo->prepare("SELECT likes FROM news_card WHERE id = ?");
                $stmt->execute([$article_id]);
                $updated = $stmt->fetch();
                
                echo json_encode([
                    'success' => true, 
                    'likes' => $updated['likes'], 
                    'is_liked' => false
                ]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
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
    <title>Nexus | <?php echo htmlspecialchars($article['title']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="style.css" rel="stylesheet">
    <link href="mobile-style.css" rel="stylesheet">
    <style>
        .back-button {
            display: inline-block;
            margin: 15px;
            font-size: 1.5em;
            color: #1da1f2;
            cursor: pointer;
        }
        .article-content {
            padding: 15px;
            line-height: 1.6;
        }
        .comment-form {
            margin: 20px 0;
            padding: 15px;
            border-top: 1px solid #e1e8ed;
        }
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e1e8ed;
            border-radius: 5px;
            resize: vertical;
            min-height: 80px;
            margin-bottom: 10px;
        }
        .comment-form button {
            background-color: #1da1f2;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
        }
        .comment {
            padding: 15px;
            border-bottom: 1px solid #e1e8ed;
            display: flex;
        }
        .comment-avatar {
            margin-right: 10px;
        }
        .comment-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .comment-content {
            flex-grow: 1;
        }
        .comment-user {
            font-weight: bold;
            margin-right: 5px;
        }
        .comment-time {
            color: #657786;
            font-size: 0.9em;
        }
        .liked {
            color: #1da1f2;
        }


        /* like btn */
        .liked {
    color: #1da1f2;
        }

        .fas.fa-heart {
            color: #1da1f2;
        }

        .tweet footer div {
            cursor: pointer;
            transition: color 0.2s;
        }

        .tweet footer div:hover {
            color: #1da1f2;
        }






        /* like animation */
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
            <div class="back-button" onclick="window.history.back();">
                <i class="fas fa-arrow-left"></i>
            </div>
            
            <article class="tweet" aria-label="News item: <?php echo htmlspecialchars($article['title']); ?>">
                <div class="tweet-header">
                    <div>
                        <span class="name">Nexus News™</span>
                        <span class="category"><?php echo htmlspecialchars(ucfirst($article['category'])); ?></span>                              
                    </div>
                </div>
               
                <?php if ($article['image_path']): ?>
                    <img
                        src="<?php echo htmlspecialchars($article['image_path']); ?>"
                        alt="Image for news: <?php echo htmlspecialchars($article['title']); ?>"
                        class="tweet-image"
                        loading="lazy"
                    />
                <?php endif; ?>                           
                <div class="tweet-header">
                    <span class="time">@nexus · <?php echo date('M j, Y g:i a', strtotime($article['created_at'])); ?></span>
                </div>
                <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                <div class="article-content"><?php echo nl2br(htmlspecialchars($article['content'])); ?></div>
                <footer>
                    <div><i class="far fa-comment"></i> <?php echo count($comments); ?></div>
                    <div onclick="toggleLike(<?php echo $article['id']; ?>, <?php echo $article['is_liked'] ? 'true' : 'false'; ?>, this)">
                        <i class="<?php echo $article['is_liked'] ? 'fas liked' : 'far'; ?> fa-heart"></i> 
                        <span class="like-count"><?php echo $article['likes']; ?></span>
                    </div>
                    <div class="<?php echo $article['is_bookmarked'] ? 'bookmarked' : ''; ?>"
                        onclick="toggleBookmark(<?php echo $article['id']; ?>, <?php echo $article['is_bookmarked'] ? 'true' : 'false'; ?>, this)">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <div><i class="fas fa-share"></i></div>
                </footer>
            </article>

            <!-- Comment Section -->
            <div class="comment-form">
                <form method="POST" id="comment-form">
                    <textarea name="comment" placeholder="Write a comment..." required></textarea>
                    <button type="submit">Post</button>
                </form>
            </div>

            <!-- Comments List -->
            <div class="comments-section">
                <h3>Comments (<?php echo count($comments); ?>)</h3>
                <?php if (empty($comments)): ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            
                            <div class="comment-content">
                                <div>
                                    <span class="comment-user"><?php echo htmlspecialchars($comment['username']); ?></span>
                                    <span class="comment-time">
                                        <?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?>
                                    </span>
                                </div>
                                <div><?php echo nl2br(htmlspecialchars($comment['content'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
        
        <?php include 'right-side-bar.php'; ?>
    </div>



    <div id="notification" class="notification" style="display: none;">
    <i class="fas fa-check"></i>
    <span id="notification-text"></span>
</div>
    
    <?php include 'mobile-menu.php'; ?>
    
    <script>
      function toggleLike(newsId, isLiked, element) {
            const action = isLiked ? 'unlike' : 'like';
            
            fetch(window.location.href, {
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
                    } else {
                        heartIcon.classList.remove('fas', 'liked');
                        heartIcon.classList.add('far');
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
        
        // Handle comment form submission
        document.getElementById('comment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload();
                }
            });
        });






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


        // Update the toggleBookmark function in view-article.php to show notifications
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
                showNotification(isBookmarked ? 'Removed from bookmarks' : 'Article bookmarked');
            }
        });
    }
    </script>
    <script src="main.js"></script>
</body>
</html>