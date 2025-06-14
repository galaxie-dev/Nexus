<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $_SESSION['anonymous'] = !isset($_SESSION['anonymous']) || !$_SESSION['anonymous'];
    if ($_SESSION['anonymous']) {
        $_SESSION['original_user'] = $_SESSION['user_id'];
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
    } else {
        $_SESSION['user_id'] = $_SESSION['original_user'];
        unset($_SESSION['original_user']);
        // Re-fetch username
        require_once 'includes/db.php';
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $_SESSION['username'] = $user['username'];
    }
}
header('Location: index.php');
exit;
?>