<?php
session_start();
require_once 'db_connect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (empty($password)) {
        $errors[] = 'Please enter your password.';
    }

    // Verify credentials
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $user['username'];
            header('Location: index.html'); // Update to index.php if needed
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Log In – Nexus</title>
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
        .form-text {
            font-size: 12px;
            color: #8899a6;
            margin-top: 5px;
            display: block;
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
        <h2>Log In to Nexus</h2>
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="POST" action="login.php" aria-label="Log in form">
            <input type="email" name="email" placeholder="Email" required aria-describedby="emailHelp" />
            <small id="emailHelp" class="form-text">Enter your registered email.</small>
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit">Log In</button>
        </form>
        <p>or</p>
        <button class="google-btn" type="button" disabled><i class="fab fa-google"></i> Sign in with Google</button>
        <p>Don't have an account? <a href="sign-up.php">Sign Up</a></p>
    </div>
</body>
</html>