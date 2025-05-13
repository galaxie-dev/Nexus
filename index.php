<?php
session_start();
require_once 'includes/db.php';

// Fetch cached news or query database
$cache_key = 'news_public_cache';
$cache_ttl = 300; // 5 minutes
$news_items = [];

if (isset($_SESSION[$cache_key]) && (time() - $_SESSION[$cache_key]['timestamp']) < $cache_ttl) {
    $news_items = $_SESSION[$cache_key]['data'];
} else {
    try {
        $stmt = $pdo->query("SELECT id, title, content, category, image_path, likes, created_at 
                            FROM news_card 
                            ORDER BY created_at DESC 
                            LIMIT 5");
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
        <!-- Left Sidebar -->
        <nav aria-label="Primary Navigation">
            <button class="close-btn" aria-label="Close menu">
                <i>NEXUS</i>
            </button>
            <ul>
                <li class="home"><a href=index.php><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                <li><i class="fas fa-search" aria-hidden="true"></i> Explore</li>
<<<<<<< HEAD
                <li><a href="login.php" ><i class="fas fa-door" aria-hidden="true"></i> Login</a></li>
                <li><a href="signup.php"><i class="fas fa-in" aria-hidden="true"></i> Sign up</a></li>
            </ul>
=======
                <li><i class="fas fa-bell" aria-hidden="true"></i> Notifications</li>
                <li><i class="fas fa-envelope" aria-hidden="true"></i> Messages</li>
                <li><i class="fas fa-bookmark" aria-hidden="true"></i> Bookmarks</li>
                <li><i class="fas fa-user" aria-hidden="true"></i> Profile</li>
                <li><a href="login.php" class="post-btn" aria-label="Log in">Log In</a></li>
                <li><a href="signup.php" class="post-btn" aria-label="Sign up">Sign Up</a></li>
            </ul>
            <!-- <button class="post-btn" type="button" disabled>Post</button> -->
>>>>>>> 9a4fe6ec06fba1722e6d4eb525dc4ffec152f423
        </nav>
        <!-- Main Content -->
        <main>
            <div class="tabs" role="tablist" aria-label="Content tabs">
                <button class="active" role="tab" aria-selected="true" tabindex="0">For you</button>
            </div>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (empty($news_items)): ?>
                <p>No news items available.</p>
            <?php else: ?>
                <?php foreach ($news_items as $news): ?>
                    <article class="tweet" aria-label="News item: <?php echo htmlspecialchars($news['title']); ?>">
                        <div class="content">
                            <?php if ($news['image_path']): ?>
                                <img
                                    src="<?php echo htmlspecialchars($news['image_path']); ?>"
                                    alt="Image for news: <?php echo htmlspecialchars($news['title']); ?>"
                                    class="tweet-image"
                                    width="600"
                                    height="300"
                                    loading="lazy"
                                />
                            <?php endif; ?>
                            <!-- <img
                                src="https://storage.googleapis.com/a1aa/image/37126454-0da0-4eb4-cfd0-c6a2ec411163.jpg"
                                alt="Profile picture of Nexus"
                                class="profile-pic"
                                width="48"
                                height="48"
                                loading="lazy"
                            /> -->
                            <header>
                                <span class="name">NEXUS™</span>
                                <span class="time">@nexus · <?php echo date('M j, Y', strtotime($news['created_at'])); ?></span>
                            </header>
                            <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                            <p class="text"><?php echo htmlspecialchars($news['content']); ?></p>
                            <p class="category">Category: <?php echo htmlspecialchars(ucfirst($news['category'])); ?></p>
                            <footer>
                                <div><i class="far fa-comment" aria-hidden="true"></i> 0</div>
                                <div><i class="far fa-heart" aria-hidden="true"></i> <?php echo $news['likes']; ?></div>
                                <div><i class="fas fa-chart-bar" aria-hidden="true"></i> 0</div>
                                <div><i class="fas fa-upload" aria-hidden="true"></i></div>
                            </footer>
                        </div>
                    </article>
                <?php endforeach; ?>
                <p class="signup-prompt">Log in or sign up to see more news and interact!</p>
            <?php endif; ?>
        </main>
        <!-- Right Sidebar -->
        <aside aria-label="Right Sidebar">
            <div class="search-box" role="search">
                <input type="search" placeholder="Search" aria-label="Search" disabled />
                <button aria-label="Search button" disabled><i class="fas fa-search" aria-hidden="true"></i></button>
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
                        <button class="follow-btn" type="button" disabled>Subscribe</button>
                    </article>
                    <article>
                        <div class="follow-left">
                            <img src="https://via.placeholder.com/32" alt="Nexus profile picture" width="32" height="32" loading="lazy" />
                            <div class="user-info">
                                <p>NEXUS</p>
                                <p class="handle">@nexus</p>
                            </div>
                        </div>
                        <button class="follow-btn" type="button" disabled>Subscribe</button>
                    </article>
                    <article>
                        <div class="follow-left">
                            <img src="https://via.placeholder.com/32" alt="News Times profile picture" width="32" height="32" loading="lazy" />
                            <div class="user-info">
                                <p>News Times</p>
                                <p class="handle">@newstimex</p>
                            </div>
                        </div>
                        <button class="follow-btn" type="button" disabled>Subscribe</button>
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
</body>
</html>