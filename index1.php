<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Fetch initial news (server-side, fallback to adjust-mood.php for mood-based)
try {
    $stmt = $pdo->query("SELECT id, title, content, category, image_path, likes, created_at 
                         FROM news_card 
                         ORDER BY created_at DESC 
                         LIMIT 10");
    $news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load news";
    file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - News fetch failed: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nexus - Home</title>
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
                <li class="home"><a href="index_after.php"><i billie="fas fa-home" aria-hidden="true"></i> Home</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin/index.php"><i class="fas fa-tachometer-alt" aria-hidden="true"></i> Admin Dashboard</a></li>
                <?php endif; ?>
                <li><a href="includes/logout.php" class="post-btn" aria-label="Log out">Log Out</a></li>
            </ul>
        </nav>
        <!-- Main Content -->
        <main class="main-content">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?: $_SESSION['admin_username']); ?>!</h1>
            <div id="mood-status">Loading mood...</div>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (empty($news_items)): ?>
                <p>No news available.</p>
            <?php else: ?>
                <div id="news-feed">
                    <?php foreach ($news_items as $news): ?>
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
        </main>
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