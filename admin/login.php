<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/index.php');
    exit;
}

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        if (adminLogin($email, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email, password, or not an admin account.';
            file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Admin login failed for $email\n", FILE_APPEND);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nexus - Admin Login</title>
    <link href="../style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
</head>
<body>
    <div class="container login-container" role="main">
        <main class="main-content">
            <h1>Admin Login</h1>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form id="admin-login-form" method="post" action="">
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="post-btn">Log In</button>
            </form>
            <p><a href="../index.php">Return to Home</a></p>
        </main>
    </div>
</body>
</html>