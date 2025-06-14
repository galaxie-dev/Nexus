<?php
// mobile-menu.php

// Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect current file
$current = basename($_SERVER['PHP_SELF']);

// Determine if user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // You can change this to your actual login check
?>


<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* ===== Mobile Menu Styling ===== */
.mobile-menu {
  display: none;
}

@media (max-width: 768px) {
  .mobile-menu {
    display: flex;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: var(--card-bg);
    border-top: 1px solid var(--border);
    padding: 0.5rem 1rem;
    justify-content: space-around;
    align-items: center;
    z-index: 9999;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
  }

  .mobile-menu-item {
    flex: 1;
    text-align: center;
    color: var(--muted);
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 500;
    transition: color 0.3s ease;
  }

  .mobile-menu-item i {
    font-size: 1.3rem;
    margin-bottom: 0.15rem;
    display: block;
  }

  .mobile-menu-item:hover {
    color: var(--primary);
  }

  .mobile-menu-item.active {
    color: var(--primary);
  }

  /* Dark mode support */
  body.dark-mode .mobile-menu {
    background-color: var(--dark-card-bg);
    border-color: var(--dark-border);
  }

  body.dark-mode .mobile-menu-item {
    color: var(--dark-muted);
  }

  body.dark-mode .mobile-menu-item:hover,
  body.dark-mode .mobile-menu-item.active {
    color: var(--primary);
  }

  main {
    padding-bottom: 70px; /* Prevent content being hidden behind menu */
  }
}

</style>
<div class="mobile-menu">
        <!-- Home is always visible -->
        <a href="<?= $current === 'index.php' ? 'index.php' : 'index1.php' ?>" class="mobile-menu-item <?= in_array($current, ['index.php', 'index1.php']) ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>

    <?php if ($current === 'index.php'): ?>
        <!-- Show Login only on index.php -->
        <a href="login.php" class="mobile-menu-item <?= $current === 'login.php' ? 'active' : '' ?>">
            <i class="fas fa-sign-in-alt"></i>
            <span>Login</span>
        </a>
    <?php else: ?>
        <!-- Show Bookmarks -->
        <a href="<?= $isLoggedIn ? 'bookmarks.php' : 'login.php' ?>" class="mobile-menu-item <?= $current === 'bookmarks.php' ? 'active' : '' ?>">
            <i class="fas fa-bookmark"></i>
            <span>Bookmarks</span>
        </a>

        <!-- Show Profile -->
        <a href="<?= $isLoggedIn ? 'user-dash.php' : 'login.php' ?>" class="mobile-menu-item <?= $current === 'user-dash.php' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>

        <!-- Logout (just visible if not on index.php) -->
        <a href="index.php" class="mobile-menu-item <?= $current === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    <?php endif; ?>
</div>