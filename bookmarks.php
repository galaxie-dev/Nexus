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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nexus - Bookmarks</title>
    <link href="style.css" rel="stylesheet">
     <link href="mobile-style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/onnx@0.0.7/dist/onnx.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/idb@7/build/umd.js"></script>
</head>
<body>
    <div class="container" role="main">
        <?php include 'navigation.php'; ?>

        <!-- Main Content -->
        <main>
            <div class="page-header">NEXUS</div>
            <h2 class="page-header">Bookmarks</h2>

            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (empty($bookmarked_news)): ?>
                <p>You have not bookmarked any news yet.</p>
            <?php else: ?>


                <div id="bookmarked-feed">
                    <?php foreach ($bookmarked_news as $news): ?>
                    <article class="tweet" aria-label="News item: <?php echo htmlspecialchars($news['title']); ?>">
                    
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
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>


                
            <?php endif; ?>
        </main>
   <?php include 'right-side-bar.php'; ?>


    </div>

        <?php include 'mobile-menu.php'; ?>
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