<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// requireAdmin();

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
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h1 class="logo">NEXUS</h1>
                <div class="admin-profile">
                    <div class="profile-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                        <p>Administrator</p>
                    </div>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../index_after.php" class="menu-item"><i class="fas fa-home"></i> <span>Home</span></a></li>
                <li><a href="index.php" class="menu-item active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="../upload_news.php" class="menu-item"><i class="fas fa-cloud-upload-alt"></i> <span>Upload News</span></a></li>
                <li><a href="manage_news.php" class="menu-item"><i class="fas fa-newspaper"></i> <span>Manage News</span></a></li>
                <li class="divider"></li>
                <li><a href="../includes/logout.php" class="menu-item logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a></li>
            </ul>
            <div class="sidebar-footer">
                <p>Nexus Admin v1.0</p>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard Overview</h1>
                <div class="header-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="theme-toggle">
                        <input type="checkbox" id="theme-switch" class="toggle-checkbox">
                        <label for="theme-switch" class="toggle-label">
                            <i class="fas fa-sun"></i>
                            <i class="fas fa-moon"></i>
                            <span class="toggle-ball"></span>
                        </label>
                    </div>
                </div>
            </header>

            <div class="dashboard-welcome">
                <div class="welcome-content">
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                    <p>Here's what's happening with Nexus today.</p>
                </div>
                <div class="welcome-stats">
                    <div class="stat-card">
                        <i class="fas fa-newspaper"></i>
                        <div>
                            <h3>24</h3>
                            <p>News Articles</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-eye"></i>
                        <div>
                            <h3>1.2K</h3>
                            <p>Daily Views</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <div>
                            <h3>356</h3>
                            <p>Active Users</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-actions">
                <div class="action-card" onclick="window.location.href='../upload_news.php'">
                    <div class="action-icon upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h3>Upload News</h3>
                    <p>Create a new news article with rich text and images</p>
                    <button class="action-btn">Get Started</button>
                </div>
                <div class="action-card" onclick="window.location.href='manage_news.php'">
                    <div class="action-icon manage-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3>Manage News</h3>
                    <p>View, edit, or delete existing news articles</p>
                    <button class="action-btn">Manage Content</button>
                </div>
                <div class="action-card">
                    <div class="action-icon analytics-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>View Analytics</h3>
                    <p>Track performance and user engagement metrics</p>
                    <button class="action-btn">View Stats</button>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="activity-content">
                            <p>Published new article "Tech Conference 2023 Highlights"</p>
                            <span class="activity-time">2 hours ago</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon warning">
                            <i class="fas fa-exclamation"></i>
                        </div>
                        <div class="activity-content">
                            <p>User "johndoe" reported a comment</p>
                            <span class="activity-time">5 hours ago</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon primary">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="activity-content">
                            <p>Updated "About Us" page content</p>
                            <span class="activity-time">Yesterday</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Theme toggle functionality
        const themeSwitch = document.getElementById('theme-switch');
        themeSwitch.addEventListener('change', function() {
            document.body.classList.toggle('dark-theme');
        });

        // Active menu item highlighting
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>