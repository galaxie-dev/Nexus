<?php require 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Nexus</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center">
  <div class="bg-gray-800 p-8 rounded-lg w-96">
    <h1 class="text-2xl font-bold text-white mb-6">NewsBlend Login</h1>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (login($_POST['email'], $_POST['password'])) {
        header("Location: index.php");
      } else {
        echo '<p class="text-red-500 mb-4">Invalid credentials</p>';
      }
    }
    ?>
    
    <form method="POST">
      <input type="email" name="email" placeholder="Email" class="w-full p-3 mb-4 bg-gray-700 text-white rounded">
      <input type="password" name="password" placeholder="Password" class="w-full p-3 mb-6 bg-gray-700 text-white rounded">
      <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700">Login</button>
    </form>
    
    <p class="text-gray-400 mt-4">
      Don't have an account? <a href="register.php" class="text-purple-400">Register</a>
    </p>
  </div>
</body>
</html>
