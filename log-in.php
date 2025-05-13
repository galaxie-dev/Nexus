<?php require 'includes/auth.php'; ?> 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Nexus</title>
  <link href="sign-log.css" rel="stylesheet" />
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" /> -->
</head>
<body>
  <div class="container">
    <h2>Welcome Back to Nexus</h2>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (login($_POST['email'], $_POST['password'])) {
        header("Location: index.php");
        exit;
      } else {
        echo '<p style="color: red; margin-bottom: 1rem;">Invalid credentials</p>';
      }
    }
    ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Log In</button>
    </form>

    <p>or</p>
    <button class="google-btn"><i class="fab fa-google"></i> Log in with Google</button>

    <p>Don't have an account? <a href="sign-up.php">Register</a></p>
  </div>
</body>
</html>
