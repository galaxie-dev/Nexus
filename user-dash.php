<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Determine active tab
$active_tab = $_GET['tab'] ?? 'posts';

// Fetch user profile
try {
    $user_stmt = $pdo->prepare("SELECT email, username, created_at FROM users WHERE id = :user_id");
    $user_stmt->execute(['user_id' => $user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format join date
    if ($user) {
        $join_date = date('F Y', strtotime($user['created_at']));
        $initials = strtoupper(substr($user['username'], 0, 1));
    }
} catch (PDOException $e) {
    $user_error = "Failed to load user profile.";
}

// Fetch stats
try {
    $stats_stmt = $pdo->prepare("SELECT 
        (SELECT COUNT(*) FROM bookmarks WHERE user_id = :user_id) as bookmark_count,
        (SELECT COUNT(*) FROM likes WHERE user_id = :user_id) as like_count,
        (SELECT COUNT(*) FROM comments WHERE user_id = :user_id) as comment_count");
    $stats_stmt->execute(['user_id' => $user_id]);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_error = "Failed to load stats.";
}

// Fetch content based on active tab
$content = '';
$error = '';

switch ($active_tab) {
    case 'liked':
        try {
            $stmt = $pdo->prepare("SELECT n.id, n.title, n.content, n.category, n.image_path, n.likes, n.created_at
                                  FROM likes l
                                  JOIN news_card n ON l.news_id = n.id
                                  WHERE l.user_id = :user_id
                                  ORDER BY l.created_at DESC");
            $stmt->execute(['user_id' => $user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($items)) {
                $content = '<div class="empty-state">
                    <i class="far fa-heart"></i>
                    <h3>No liked posts yet</h3>
                    <p>Articles you like will appear here</p>
                </div>';
            } else {
                $content = '<div class="content-grid">';
                foreach ($items as $item) {
                    $content .= renderNewsCard($item);
                }
                $content .= '</div>';
            }
        } catch (PDOException $e) {
            $error = "Failed to load liked posts.";
        }
        break;
        
    case 'bookmarks':
        try {
            $stmt = $pdo->prepare("SELECT n.id, n.title, n.content, n.category, n.image_path, n.likes, n.created_at
                                  FROM bookmarks b
                                  JOIN news_card n ON b.news_id = n.id
                                  WHERE b.user_id = :user_id
                                  ORDER BY b.created_at DESC");
            $stmt->execute(['user_id' => $user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($items)) {
                $content = '<div class="empty-state">
                    <i class="far fa-bookmark"></i>
                    <h3>No bookmarks yet</h3>
                    <p>Save articles to read later</p>
                </div>';
            } else {
                $content = '<div class="content-grid">';
                foreach ($items as $item) {
                    $content .= renderNewsCard($item);
                }
                $content .= '</div>';
            }
        } catch (PDOException $e) {
            $error = "Failed to load bookmarks.";
        }
        break;
        
    case 'comments':
        try {
            $stmt = $pdo->prepare("SELECT c.id, c.content, c.created_at, n.id as news_id, n.title
                                  FROM comments c
                                  JOIN news_card n ON c.news_id = n.id
                                  WHERE c.user_id = :user_id
                                  ORDER BY c.created_at DESC");
            $stmt->execute(['user_id' => $user_id]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($comments)) {
                $content = '<div class="empty-state">
                    <i class="far fa-comment"></i>
                    <h3>No comments yet</h3>
                    <p>Your comments will appear here</p>
                </div>';
            } else {
                $content = '<div class="comments-list">';
                foreach ($comments as $comment) {
                    $content .= '<div class="comment-card">
                        <div class="comment-header">
                            <a href="view-article.php?id='.$comment['news_id'].'" class="comment-article">'.$comment['title'].'</a>
                            <span class="comment-date">'.date('M j, Y g:i a', strtotime($comment['created_at'])).'</span>
                        </div>
                        <div class="comment-content">'.htmlspecialchars($comment['content']).'</div>
                    </div>';
                }
                $content .= '</div>';
            }
        } catch (PDOException $e) {
            $error = "Failed to load comments.";
        }
        break;
        
    default: // posts
        $content = '<div class="empty-state">
            <i class="far fa-edit"></i>
            <h3>Create Article</h3>
            <p>You cannot post articles yet</p>
            <p class="small">Contact admin for publishing privileges</p>
        </div>';
        break;
}

function renderNewsCard($item) {
    return '<a href="view-article.php?id='.$item['id'].'" class="news-card">
        '.($item['image_path'] ? 
            '<img src="'.htmlspecialchars($item['image_path']).'" alt="'.htmlspecialchars($item['title']).'" class="news-image">' : 
            '<div class="news-image placeholder"><i class="far fa-newspaper"></i></div>').'
        <div class="news-content">
            <span class="news-category">'.htmlspecialchars(ucfirst($item['category'])).'</span>
            <h3 class="news-title">'.htmlspecialchars($item['title']).'</h3>
            <p class="news-excerpt">'.htmlspecialchars(substr($item['content'], 0, 120)).'...</p>
            <div class="news-meta">
                <span class="news-date">'.date('M j, Y', strtotime($item['created_at'])).'</span>
                <span class="news-likes"><i class="fas fa-heart"></i> '.$item['likes'].'</span>
            </div>
        </div>
    </a>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Nexus | Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="mobile-style.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3a86ff;
            --primary-light: rgba(58, 134, 255, 0.1);
            --bg: #f8faff;
            --text: #0a1f44;
            --muted: #7a859f;
            --card-bg: #ffffff;
            --border: #e4e9f2;
            --hover: rgba(58, 134, 255, 0.08);
            --dark-bg: #0f0f17;
            --dark-text: #f0f4ff;
            --dark-card-bg: #1a1a26;
            --dark-border: #2a2a3a;
            --dark-muted: #8a93b0;
            --font: 'Inter', -apple-system, sans-serif;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --transition: all 0.4s cubic-bezier(0.2, 0.8, 0.4, 1);
            --glass: rgba(255, 255, 255, 0.85);
            --glass-dark: rgba(15, 15, 23, 0.85);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            --shadow-dark: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        body {
            font-family: var(--font);
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            display: flex;
            grid-template-columns: auto 1fr auto;
            min-height: 100vh;
        }

        main {
            /* padding: 0;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border); */


              flex: 1;
    max-width: 700px;
    margin: 0 auto; /* This centers the content */
    padding: 2rem;
    width: 100%; /* Add this to ensure consistent width */
        }

        .profile-header {
            padding: 1.5rem;
            position: relative;
        }

        .profile-cover {
            height: 150px;
            background: linear-gradient(135deg, var(--primary), #2667cc);
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -50px auto 1rem auto;
            color: var(--primary);
            font-size: 2.5rem;
            font-weight: 600;
            border: 4px solid var(--card-bg);
            box-shadow: var(--shadow);
        }

        .profile-info {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .profile-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
        }

        .profile-meta {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .stats-grid {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1.5rem;
            padding: 0 1.5rem;
        }

        .stat-item {
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .stat-item:hover {
            color: var(--primary);
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
        }

        .profile-tab {
            flex: 1;
            text-align: center;
            padding: 1rem;
            font-weight: 500;
            color: var(--muted);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .profile-tab:hover {
            color: var(--primary);
            background: var(--hover);
        }

        .profile-tab.active {
            color: var(--primary);
        }

        .profile-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
        }

        .tab-content {
            padding: 1.5rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .news-card {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .news-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }

        .news-image.placeholder {
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
        }

        .news-image.placeholder i {
            font-size: 2rem;
        }

        .news-content {
            padding: 1.25rem;
        }

        .news-category {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
        }

        .news-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            line-height: 1.4;
        }

        .news-excerpt {
            color: var(--muted);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--muted);
        }

        .news-likes i {
            color: #ff375f;
            margin-right: 0.25rem;
        }

        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .comment-card {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .comment-article {
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }

        .comment-date {
            font-size: 0.8rem;
            color: var(--muted);
        }

        .comment-content {
            line-height: 1.5;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--border);
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin: 0 0 0.5rem 0;
            color: var(--text);
        }

        .empty-state p {
            margin: 0.5rem 0;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-state p.small {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            main {
                border-left: none;
                border-right: none;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-cover {
                height: 120px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
                margin-top: -40px;
            }
        }

        /* Dark mode styles */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: var(--dark-bg);
                color: var(--dark-text);
            }
            
            main {
                border-left-color: var(--dark-border);
                border-right-color: var(--dark-border);
            }
            
            .profile-avatar {
                border-color: var(--dark-card-bg);
            }
            
            .news-card, .comment-card {
                background: var(--dark-card-bg);
                border-color: var(--dark-border);
            }
            
            .news-title, .empty-state h3 {
                color: var(--dark-text);
            }
            
            .news-excerpt, .news-meta, .comment-date, .empty-state {
                color: var(--dark-muted);
            }
            
            .news-image.placeholder {
                background: var(--dark-bg);
                color: var(--dark-muted);
            }
            
            .profile-tabs {
                border-bottom-color: var(--dark-border);
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main">
        <?php include 'navigation.php'; ?>
        
        <main>
            <div class="profile-cover"></div>
            
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo $initials ?? 'U'; ?>
                </div>
                
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></h1>
                    <div class="profile-meta">
                        <span><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
                        <span> · Joined in <?php echo $join_date ?? 'M j Y'; ?></span>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-item" onclick="window.location.href='?tab=posts'">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Posts</div>
                    </div>
                    <div class="stat-item" onclick="window.location.href='?tab=liked'">
                        <div class="stat-value"><?php echo $stats['like_count'] ?? 0; ?></div>
                        <div class="stat-label">Likes</div>
                    </div>
                    <div class="stat-item" onclick="window.location.href='?tab=bookmarks'">
                        <div class="stat-value"><?php echo $stats['bookmark_count'] ?? 0; ?></div>
                        <div class="stat-label">Bookmarks</div>
                    </div>
                    <div class="stat-item" onclick="window.location.href='?tab=comments'">
                        <div class="stat-value"><?php echo $stats['comment_count'] ?? 0; ?></div>
                        <div class="stat-label">Comments</div>
                    </div>
                </div>
            </div>
            
            <div class="profile-tabs">
                <div class="profile-tab <?php echo $active_tab === 'posts' ? 'active' : ''; ?>" onclick="window.location.href='?tab=posts'">
                    <i class="far fa-edit"></i> Posts
                </div>
                <div class="profile-tab <?php echo $active_tab === 'liked' ? 'active' : ''; ?>" onclick="window.location.href='?tab=liked'">
                    <i class="far fa-heart"></i> Liked
                </div>
                <div class="profile-tab <?php echo $active_tab === 'bookmarks' ? 'active' : ''; ?>" onclick="window.location.href='?tab=bookmarks'">
                    <i class="far fa-bookmark"></i> Bookmarks
                </div>
                <div class="profile-tab <?php echo $active_tab === 'comments' ? 'active' : ''; ?>" onclick="window.location.href='?tab=comments'">
                    <i class="far fa-comment"></i> Comments
                </div>
            </div>
            
            <div class="tab-content">
                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php else: ?>
                    <?php echo $content; ?>
                <?php endif; ?>
            </div>
        </main>
        
        <?php include 'right-side-bar.php'; ?>
    </div>
    
    <?php include 'mobile-menu.php'; ?>
    
    <script>
        // Add smooth transitions when switching tabs
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.profile-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Add hover effects to cards
            const cards = document.querySelectorAll('.news-card, .comment-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transition = 'var(--transition)';
                });
            });
        });
    </script>
</body>
</html>