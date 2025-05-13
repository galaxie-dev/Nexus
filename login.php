<?php
require_once 'includes/auth.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (login($email, $password)) {
        header('Location: index1.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Nexus</title>
    <link href="style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a, #2c2c2c);
            overflow: hidden;
        }
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            background: transparent;
        }
        .stars::before {
            content: '';
            position: absolute;
            width: 2px;
            height: 2px;
            background: white;
            border-radius: 50%;
            animation: twinkle 2s infinite;
            box-shadow: /* Add multiple star positions */
                100px 200px #fff, 300px 400px #fff, 500px 100px #fff,
                700px 300px #fff, 200px 600px #fff, 400px 500px #fff,
                600px 200px #fff, 800px 400px #fff, 100px 500px #fff;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: #222;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .login-container h1 {
            text-align: center;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #333;
            border-radius: 5px;
            background: #333;
            color: #e0e0e0;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background: #1da1f2;
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
        }
        .login-container button:hover {
            background: #0d91e2;
        }
        .login-container p {
            text-align: center;
            color: #657786;
            margin-top: 15px;
        }
        .login-container a {
            color: #1da1f2;
            text-decoration: none;
        }
        .login-container a:hover {
            text-decoration: underline;
        }
        .error {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="stars"></div>
    <div class="login-container">
        <h1>Nexus Login</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
</body>
</html>