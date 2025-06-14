<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch user data
try {
    $user_stmt = $pdo->prepare("SELECT email, username FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Count bookmarks
    $bookmark_stmt = $pdo->prepare("SELECT COUNT(*) as bookmark_count FROM bookmarks WHERE user_id = ?");
    $bookmark_stmt->execute([$user_id]);
    $bookmark_count = $bookmark_stmt->fetch(PDO::FETCH_ASSOC)['bookmark_count'];
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $user = ['username' => 'User', 'email' => 'user@example.com'];
    $bookmark_count = 0;
}

// Generate initials from username
$initials = '';
if (!empty($user['username'])) {
    $name_parts = explode(' ', $user['username']);
    foreach ($name_parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    $initials = substr($initials, 0, 2);
} else {
    $initials = 'US';
}
?>

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

/* Ultra-Premium Navigation */
nav {
  width: 280px;
  background: var(--glass);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  padding: 2rem 1.5rem;
  border-right: 1px solid var(--border);
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  transition: var(--transition);
}

body.dark-mode nav {
  background: var(--glass-dark);
  border-right: 1px solid var(--dark-border);
}

.logo {
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--primary);
  margin-bottom: 3rem;
  font-family: 'Inter', sans-serif;
  display: flex;
  align-items: center;
  padding-left: 12px;
  position: relative;
}

.logo:after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -8px;
  width: 40px;
  height: 4px;
  background: var(--primary);
  border-radius: 2px;
  transition: var(--transition);
}

nav ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex-grow: 1;
}

nav li {
  position: relative;
  overflow: hidden;
}

nav a {
  display: flex;
  align-items: center;
  padding: 14px 16px;
  color: var(--text);
  text-decoration: none;
  border-radius: var(--radius-md);
  font-weight: 500;
  transition: var(--transition);
  position: relative;
  z-index: 1;
}

body.dark-mode nav a {
  color: var(--dark-text);
}

nav a:hover {
  color: var(--primary);
  transform: translateX(4px);
}

nav a.active {
  color: var(--primary);
  font-weight: 600;
  background: var(--primary-light);
}

nav a.active:before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: var(--primary);
  border-radius: 0 3px 3px 0;
}

nav i {
  margin-right: 14px;
  font-size: 1.2rem;
  width: 24px;
  text-align: center;
  transition: var(--transition);
}

nav a:hover i {
  transform: scale(1.1);
}

/* User profile at bottom */
.user-profile {
  margin-top: auto;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 12px;
}

body.dark-mode .user-profile {
  border-top: 1px solid var(--dark-border);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
}

.user-info {
  flex-grow: 1;
  overflow: hidden;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-handle {
  font-size: 0.85rem;
  color: var(--muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.logout-btn {
  background: none;
  border: none;
  color: var(--muted);
  cursor: pointer;
  transition: var(--transition);
}

.logout-btn:hover {
  color: var(--primary);
  transform: rotate(15deg);
}

/* Floating indicator */
.nav-indicator {
  position: absolute;
  right: -10px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  background: var(--primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(58, 134, 255, 0.3);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: translateY(-50%) scale(1); }
  50% { transform: translateY(-50%) scale(1.1); }
  100% { transform: translateY(-50%) scale(1); }
}

/* Responsive */
@media (max-width: 1024px) {
  nav {
    transform: translateX(-100%);
    box-shadow: 0 0 0 100vmax rgba(0,0,0,0);
    transition: var(--transition), box-shadow 0.3s ease;
  }
  
  nav.active {
    transform: translateX(0);
    box-shadow: 0 0 0 100vmax rgba(0,0,0,0.5);
  }
  
  .logo {
    justify-content: center;
    padding-left: 0;
  }
  
  .logo:after {
    left: 50%;
    transform: translateX(-50%);
  }
}
</style>

<!-- Ultra-Premium Navigation -->
<nav aria-label="Primary Navigation">
  <div class="logo">NEXUS</div>
  <ul>
    <li>
      <a href="index1.php" class="<?= $current_page === 'index1.php' ? 'active' : '' ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
      </a>
    </li>
    <li>
      <a href="bookmarks.php" class="<?= $current_page === 'bookmarks.php' ? 'active' : '' ?>">
        <i class="fas fa-bookmark"></i>
        <span>Bookmarks</span>
        <?php if ($bookmark_count > 0): ?>
          <div class="nav-indicator"><?= $bookmark_count ?></div>
        <?php endif; ?>
      </a>
    </li>
    <li>
      <a href="user-dash.php" class="<?= $current_page === 'user-dash.php' ? 'active' : '' ?>">
        <i class="fas fa-user"></i>
        <span>Profile</span>
      </a>
    </li>
    <li>
      <a href="#" class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
      </a>
    </li>
    <li>
      <a href="#" id="dark-mode-toggle">
        <i class="fas fa-moon"></i>
        <span>Dark Mode</span>
      </a>
    </li>
  </ul>
  
  <div class="user-profile">
    <div class="user-avatar"><?= $initials ?></div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($user['username']) ?></div>
     
    </div>
    <a href="index.php" class="logout-btn" aria-label="Log out">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</nav>

<script>
// Dark mode toggle functionality
document.getElementById('dark-mode-toggle').addEventListener('click', function(e) {
  e.preventDefault();
  document.body.classList.toggle('dark-mode');
  
  // Save preference to localStorage
  const isDarkMode = document.body.classList.contains('dark-mode');
  localStorage.setItem('darkMode', isDarkMode);
});

// Check for saved dark mode preference
if (localStorage.getItem('darkMode') === 'true') {
  document.body.classList.add('dark-mode');
}
</script>