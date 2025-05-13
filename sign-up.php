<?php
session_start();
require_once 'db_connect.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be 3-50 characters long.';
    }
    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check for duplicate email or username
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $errors[] = 'Email or username is already registered.';
        }
    }

    // Register user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$email, $username, $hashed_password]);
            $success = 'Registration successful! Please log in.';
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign Up – Nexus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000;
            margin: 0;
            padding: 0;
            position: relative;
            overflow-x: hidden;
        }
        /* Star Animation */
        body::before, body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: transparent;
            pointer-events: none;
        }
        body::before {
            box-shadow: 
                10vw 20vh 1px rgba(255, 255, 255, 0.8),
                30vw 50vh 1px rgba(255, 255, 255, 0.6),
                50vw 10vh 1px rgba(255, 255, 255, 0.7),
                70vw 80vh 1px rgba(255, 255, 255, 0.9),
                90vw 30vh 1px rgba(255, 255, 255, 0.5);
            animation: twinkle 4s infinite;
        }
        body::after {
            box-shadow: 
                20vw 60vh 1px rgba(255, 255, 255, 0.7),
                40vw 30vh 1px rgba(255, 255, 255, 0.8),
                60vw 70vh 1px rgba(255, 255, 255, 0.6),
                80vw 20vh 1px rgba(255, 255, 255, 0.9),
                95vw 50vh 1px rgba(255, 255, 255, 0.5);
            animation: twinkle 5s infinite 1s;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 1; }
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #1c2938;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        h2 {
            font-size: 24px;
            color: #ffffff;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input {
            padding: 10px;
            font-size: 16px;
            background-color: #253341;
            color: #ffffff;
            border: 1px solid #38444d;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #1da1f2;
            box-shadow: 0 0 5px rgba(29, 161, 242, 0.5);
        }
        button {
            padding: 12px;
            background-color: #1da1f2;
            color: #ffffff;
            border: none;
            border-radius: 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }
        button:hover {
            background-color: #0d91e2;
            transform: scale(1.02);
        }
        .google-btn {
            background-color: #ffffff;
            color: #14171a;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .google-btn:hover {
            background-color: #e0e0e0;
        }
        .errors {
            list-style: none;
            padding: 0;
            margin: 10px 0;
            color: #f44336;
            font-size: 14px;
        }
        .success {
            color: #4caf50;
            font-size: 14px;
            margin: 10px 0;
        }
        p {
            font-size: 14px;
            color: #8899a6;
            margin: 15px 0;
        }
        a {
            color: #1da1f2;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        @media (max-width: 500px) {
            .container {
                margin: 20px;
                padding: 15px;
            }
            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main">
        <h2>Create a Nexus Account</h2>
        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="POST" action="signup.php" aria-label="Sign up form">
            <input type="email" name="email" placeholder="Email" required aria-describedby="emailHelp" />
            <small id="emailHelp" class="form-text">We'll never share your email.</small>
            <input type="text" name="username" placeholder="Username" required aria-describedby="usernameHelp" />
            <small id="usernameHelp" class="form-text">Choose a unique username (3-50 characters).</small>
            <input type="password" name="password" placeholder="Password" required aria-describedby="passwordHelp" />
            <small id="passwordHelp" class="form-text">Password must be at least 8 characters.</small>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
            <button type="submit">Sign Up</button>
        </form>
        <p>or</p>
        <button class="google-btn" type="button" disabled><i class="fab fa-google"></i> Sign in with Google</button>
        <p>Already have an account? <a href="log-in.php">Log In</a></p>
    </div>
</body>
</html>