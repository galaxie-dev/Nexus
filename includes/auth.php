<?php
session_start();
require_once 'db.php';

function register($email, $username, $password) {
    global $pdo;
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
    return $stmt->execute([$email, $username, $hashed]);
}

function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_unset();
    session_destroy();
}

function isAdmin() {
    if (!isAdminLoggedIn()) {
        return false;
    }
    return true; // Already verified is_admin in adminLogin
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function requireAdmin() {
    requireAdminLogin();
}

function adminLogin($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password']) && $user['is_admin'] == 1) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        return true;
    }
    return false;
}
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}


?>