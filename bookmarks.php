<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$user_id = $_SESSION['user_id'];

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
    <title>Nexus - Bookmarks</title>
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/onnx@0.0.7/dist/onnx.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/idb@7/build/umd.js"></script>
</head>
<body>
    <div class="container" role="main">
        <!-- Fixed Sidebar -->
        <nav class="fixed-sidebar" aria-label="Primary Navigation">
            <button class="close-btn" aria-label="Close menu"><i>NEXUS</i></button>
            <ul>
                <li class="home"><a style="text-decoration: none;" href=index1.php><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                <!-- <li><i class="fas fa-search" aria-hidden="true"></i> Explore</li>   -->
                <!-- <li><i class="fas fa-bell" aria-hidden="true"></i> Notifications</li> -->
                <!-- <li><i class="fas fa-envelope" aria-hidden="true"></i> Messages</li> -->
                <li><a href="bookmarks.php" style="text-decoration: none;"><i class="fas fa-bookmark" aria-hidden="true"></i> Bookmarks</a></li>
                <li><i class="fas fa-user" aria-hidden="true"><a href=user-dash.php></i> Profile</li>          
            
                <!-- <li><a href="login.php" style="text-decoration: none;"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Login</a></li> -->
                <!-- <li><a href="signup.php"><i class="fas fa-in" aria-hidden="true"></i> Sign up</a></li> -->
            
            </ul>
        </nav>
        <!-- Main Content -->
        <section>
            <h2>Your Bookmarked News</h2>

            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (empty($bookmarked_news)): ?>
                <p>You have not bookmarked any news yet.</p>
            <?php else: ?>
                <div id="bookmarked-feed">
                    <?php foreach ($bookmarked_news as $news): ?>
                        <article class="tweet" data-news-id="<?php echo $news['id']; ?>">
                            <div class="tweet-header">
                                <div>
                                    <span class="name">Nexus News</span>
                                    <span class="category"><?php echo htmlspecialchars(ucfirst($news['category'])); ?></span>
                                    <span class="time"><?php echo date('M j, Y', strtotime($news['created_at'])); ?></span>
                                </div>
                            </div>
                            <h3 class="tweet-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                            <div class="tweet-text"><?php echo htmlspecialchars(substr($news['content'], 0, 200)); ?>...</div>
                            <?php if ($news['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($news['image_path']); ?>" class="tweet-image" alt="News image">
                            <?php endif; ?>
                            <div class="tweet-footer">
                                <div>
                                    <button class="like-btn" data-news-id="<?php echo $news['id']; ?>">
                                        <i class="fas fa-heart"></i> <span><?php echo $news['likes']; ?></span>
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>



    </div>
    <script src="assets/js/app.js"></script>
    <script>
        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(() => console.log('Service Worker Registered'))
                .catch(err => console.error('Service Worker Error:', err));
        }

        // Initialize IndexedDB
        async function initDB() {
            const db = await idb.openDB('nexus-db', 1, {
                upgrade(db) {
                    db.createObjectStore('news', { keyPath: 'id' });
                }
            });
            // Cache news from server
            if (navigator.onLine) {
                fetch('/api/news.php')
                    .then(res => res.json())
                    .then(news => {
                        const tx = db.transaction('news', 'readwrite');
                        news.forEach(item => tx.store.put(item));
                        return tx.done;
                    })
                    .catch(err => console.error('News cache error:', err));
            }
            // Display offline news
            if (!navigator.onLine) {
                const news = await db.getAll('news');
                displayNews(news);
            }
        }
        initDB();

        // Display news function
        function displayNews(newsItems) {
            const feed = document.getElementById('news-feed');
            feed.innerHTML = '';
            if (!newsItems.length) {
                feed.innerHTML = '<p>No news available offline.</p>';
                return;
            }
            newsItems.forEach(news => {
                const article = document.createElement('article');
                article.className = 'tweet';
                article.dataset.newsId = news.id;
                article.innerHTML = `
                    <div class="tweet-header">
                        <div>
                            <span class="name">Nexus News</span>
                            <span class="category">${news.category}</span>
                            <span class="time">${new Date(news.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                        </div>
                    </div>
                    <h3 class="tweet-title">${news.title}</h3>
                    <div class="tweet-text">${news.content.substring(0, 200)}...</div>
                    ${news.image_path ? `<img src="${news.image_path}" class="tweet-image" alt="News image">` : ''}
                    <div class="tweet-footer">
                        <div>
                            <button class="like-btn" data-news-id="${news.id}">
                                <i class="fas fa-heart"></i> <span>${news.likes}</span>
                            </button>
                        </div>
                    </div>
                `;
                feed.appendChild(article);
            });
        }

        // Mood detection and content adjustment
        async function updateMood() {
            try {
                // Fetch features
                const featuresRes = await fetch('/features.php');
                const features = await featuresRes.json();
                // Predict mood (client-side ONNX)
                const session = await ort.InferenceSession.create('/mood_model.onnx');
                const tensor = new ort.Tensor('float32', [features.avg_scroll || 0, features.avg_dwell || 0], [1, 2]);
                const feeds = { input: tensor };
                const results = await session.run(feeds);
                const mood = results.output.data[0] > 0.5 ? 'positive' : 'negative';
                document.getElementById('mood-status').textContent = `Mood: ${mood}`;
                // Fetch mood-based news
                const newsRes = await fetch(`/adjust-mood.php?mood=${mood}`);
                const news = await newsRes.json();
                displayNews(news);
                // Update IndexedDB
                const db = await idb.openDB('nexus-db', 1);
                const tx = db.transaction('news', 'readwrite');
                news.forEach(item => tx.store.put(item));
                await tx.done;
            } catch (err) {
                console.error('Mood update error:', err);
                document.getElementById('mood-status').textContent = 'Mood detection failed';
            }
        }
        // Initial mood update
        if (navigator.onLine) {
            updateMood();
        }
    </script>
</body>
</html>