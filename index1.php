<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if not logged in
if (!isLoggedIn() && !(isset($_SESSION['anonymous']) && $_SESSION['anonymous'])) {
    header('Location: index.php');
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch cached news or query database
$cache_key = 'news_user_cache';
$cache_ttl = 300; // 5 minutes
$news_items = [];

if (isset($_SESSION[$cache_key]) && (time() - $_SESSION[$cache_key]['timestamp']) < $cache_ttl) {
    $news_items = $_SESSION[$cache_key]['data'];
} else {
    try {
        $stmt = $pdo->query("SELECT id, title, content, category, image_path, likes, created_at 
                            FROM news_card 
                            ORDER BY created_at DESC 
                            LIMIT 10");
        $news_items = $stmt->fetchAll();
        $_SESSION[$cache_key] = [
            'data' => $news_items,
            'timestamp' => time()
        ];
    } catch (PDOException $e) {
        $news_items = [];
        $error = "Failed to load news: " . $e->getMessage();
        error_log('News fetch failed: ' . $e->getMessage());
    }
}

$is_anonymous = isset($_SESSION['anonymous']) && $_SESSION['anonymous'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nexus</title>
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
</head>
<body>
    <div class="container" role="main">
        <!-- Fixed Sidebar -->
        <nav class="fixed-sidebar" aria-label="Primary Navigation">
            <button class="close-btn" aria-label="Close menu">
                <i>NEXUS</i>
            </button>
            <ul>
                <li class="home"><i class="fas fa-home" aria-hidden="true"></i> Home</li>
                <li><i class="fas fa-search" aria-hidden="true"></i> Explore</li>
                <li><i class="fas fa-bell" aria-hidden="true"></i> Notifications</li>
                <li><i class="fas fa-envelope" aria-hidden="true"></i> Messages</li>
                <li><i class="fas fa-bookmark" aria-hidden="true"></i> Bookmarks</li>
                <li><i class="fas fa-user" aria-hidden="true"></i> Profile</li>
                <li>
                    <i class="fas fa-user-circle" aria-hidden="true"></i> 
                    <?php echo $is_anonymous ? 'Anonymous User' : htmlspecialchars($_SESSION['username']); ?>
                </li>
                <?php if (!$is_anonymous): ?>
                    <li>
                        <a href="includes/logout.php" class="post-btn" aria-label="Log out">Log Out</a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="anonymous.php" class="post-btn" aria-label="Toggle anonymous mode">
                        <?php echo $is_anonymous ? 'Exit Anonymous Mode' : 'Anonymous Mode'; ?>
                    </a>
                </li>
            </ul>
            <button class="post-btn" type="button">Post</button>
        </nav>
        <!-- Main Content -->
        <main class="main-content">
            <div class="tabs" role="tablist" aria-label="Content tabs">
                <button class="active" role="tab" aria-selected="true" tabindex="0">For you</button>
                <button role="tab" aria-selected="false" tabindex="-1">Following</button>
            </div>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (empty($news_items)): ?>
                <p>No news items available.</p>
            <?php else: ?>
                <?php foreach ($news_items as $news): ?>
                    <?php
                    // Get comment count and comments
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE news_id = ?");
                    $stmt->execute([$news['id']]);
                    $comment_count = $stmt->fetchColumn();

                    $stmt = $pdo->prepare("SELECT c.id, c.content, c.created_at, u.username 
                                         FROM comments c 
                                         JOIN users u ON c.user_id = u.id 
                                         WHERE c.news_id = ? 
                                         ORDER BY c.created_at DESC 
                                         LIMIT 5");
                    $stmt->execute([$news['id']]);
                    $comments = $stmt->fetchAll();

                    // Check if user liked
                    $liked = false;
                    if (isLoggedIn() && !$is_anonymous) {
                        $stmt = $pdo->prepare("SELECT clicks FROM user_behavior WHERE user_id = ? AND news_id = ?");
                        $stmt->execute([$_SESSION['user_id'], $news['id']]);
                        $result = $stmt->fetch();
                        $liked = $result && $result['clicks'] > 0;
                    }
                    ?>
                    <article class="tweet" aria-label="News item: <?php echo htmlspecialchars($news['title']); ?>" data-news-id="<?php echo $news['id']; ?>">
                        <div class="content">
                            <div class="tweet-header">
                                <img
                                    src="https://storage.googleapis.com/a1aa/image/37126454-0da0-4eb4-cfd0-c6a2ec411163.jpg"
                                    alt="Profile picture of Nexus"
                                    class="profile-pic"
                                    width="48"
                                    height="48"
                                    loading="lazy"
                                />
                                <div>
                                    <span class="name">NEXUS™</span>
                                    <span class="time">@nexus · <?php echo date('M j, Y', strtotime($news['created_at'])); ?></span>
                                </div>
                                <button class="more-btn" aria-label="More options"><i class="far fa-bookmark" aria-hidden="true"></i></button>
                            </div>
                            <h3 class="tweet-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                            <p class="tweet-text"><?php echo htmlspecialchars($news['content']); ?></p>
                            <?php if ($news['image_path']): ?>
                                <img
                                    src="<?php echo htmlspecialchars($news['image_path']); ?>"
                                    alt="Image for news: <?php echo htmlspecialchars($news['title']); ?>"
                                    class="tweet-image"
                                    loading="lazy"
                                />
                            <?php endif; ?>
                            <p class="category">Category: <?php echo htmlspecialchars(ucfirst($news['category'])); ?></p>
                            <footer class="tweet-footer">
                                <div>
                                    <button class="comment-btn" data-news-id="<?php echo $news['id']; ?>" <?php echo $is_anonymous ? 'disabled' : ''; ?>>
                                        <i class="far fa-comment" aria-hidden="true"></i> 
                                        <span id="comments<?php echo $news['id']; ?>"><?php echo $comment_count; ?></span>
                                    </button>
                                </div>
                                <div>
                                    <button class="like-btn <?php echo $liked ? 'liked' : ''; ?>" 
                                            data-news-id="<?php echo $news['id']; ?>" 
                                            <?php echo $is_anonymous ? 'disabled' : ''; ?>>
                                        <i class="<?php echo $liked ? 'fas' : 'far'; ?> fa-heart" aria-hidden="true"></i> 
                                        <span id="likes<?php echo $news['id']; ?>"><?php echo $news['likes']; ?></span>
                                    </button>
                                </div>
                                <div>
                                    <button class="share-btn" data-news-id="<?php echo $news['id']; ?>" data-platform="whatsapp">
                                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                    </button>
                                    <button class="share-btn" data-news-id="<?php echo $news['id']; ?>" data-platform="twitter">
                                        <i class="fab fa-twitter" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </footer>
                            <?php if (!$is_anonymous): ?>
                                <form class="comment-form" data-news-id="<?php echo $news['id']; ?>" style="display: none;">
                                    <textarea placeholder="Tweet your reply..." maxlength="280"></textarea>
                                    <button type="submit">Tweet</button>
                                </form>
                            <?php endif; ?>
                            <div class="comments-section" data-news-id="<?php echo $news['id']; ?>">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment" data-comment-id="<?php echo $comment['id']; ?>">
                                        <span class="comment-username">@<?php echo htmlspecialchars($comment['username']); ?></span>
                                        <span class="comment-time"><?php echo date('M j, Y H:i', strtotime($comment['created_at'])); ?></span>
                                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
        <!-- Right Sidebar -->
        <aside class="right-sidebar" aria-label="Right Sidebar">
            <div class="search-box" role="search">
                <input type="search" placeholder="Search" aria-label="Search" />
                <button aria-label="Search button"><i class="fas fa-search" aria-hidden="true"></i></button>
            </div>
            <section class="card" aria-label="What's happening">
                <h2>Suggested For You</h2>
                <div class="trending-list">
                    <div>
                        <p>Based on recent activity</p>
                        <p class="trend-title">KCSE Results Released</p>
                        <p>Top candidate scores A plain of 84 points</p>
                        <button class="more-btn" aria-label="More options for KCSE">...</button>
                    </div>
                    <div>
                        <p>Trending in politics</p>
                        <p class="trend-title">Mike Mueni to Run for Presidency</p>
                        <p>Mike Mueni registers his Revolution Party and confirms</p>
                        <button class="more-btn" aria-label="More options for Mike Mueni">...</button>
                    </div>
                    <div>
                        <p>Trending in sports</p>
                        <p class="trend-title">Man United Thrashes Leeds United</p>
                        <p>Home derby win for the reds as they eye Premier League title</p>
                        <button class="more-btn" aria-label="More options for Leeds">...</button>
                    </div>
                    <button class="more-btn" aria-label="Show more trending topics">Show more</button>
                </div>
            </section>
            <section class="card" aria-label="Who to follow">
                <h2>Subscribe to these News Outlets</h2>
                <div class="follow-list">
                    <article>
                        <div class="follow-left">
                            <img src="https://via.placeholder.com/32" alt="NASA profile picture" width="32" height="32" loading="lazy" />
                            <div class="user-info">
                                <p>NASA</p>
                                <p class="handle">@nasa</p>
                            </div>
                        </div>
                        <button class="follow-btn" type="button">Subscribe</button>
                    </article>
                    <article>
                        <div class="follow-left">
                            <img src="https://via.placeholder.com/32" alt="Nexus profile picture" width="32" height="32" loading="lazy" />
                            <div class="user-info">
                                <p>NEXUS</p>
                                <p class="handle">@nexus</p>
                            </div>
                        </div>
                        <button class="follow-btn" type="button">Subscribe</button>
                    </article>
                    <article>
                        <div class="follow-left">
                            <img src="https://via.placeholder.com/32" alt="News Times profile picture" width="32" height="32" loading="lazy" />
                            <div class="user-info">
                                <p>News Times</p>
                                <p class="handle">@newstimex</p>
                            </div>
                        </div>
                        <button class="follow-btn" type="button">Subscribe</button>
                    </article>
                </div>
                <button class="more-btn" aria-label="Show more who to follow">Show more</button>
            </section>
            <footer class="footer" aria-label="Footer">
                <p>Terms of Service</p>
                <span>|</span>
                <p>Privacy Policy</p>
                <span>|</span>
                <p>Cookie Policy</p>
            </footer>
        </aside>
    </div>
    <script>
        // Pass CSRF token to JavaScript
        const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>