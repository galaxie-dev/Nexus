<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireAdmin();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "nexus";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $conn->real_escape_string($_POST['title']);
  $content = $conn->real_escape_string($_POST['content']);

  // Use custom category if "other" is selected
  $category = $_POST['category'] === 'other' && !empty($_POST['custom_category'])
    ? $conn->real_escape_string($_POST['custom_category'])
    : $conn->real_escape_string($_POST['category']);

  // Handle image upload
  $imagePath = "";
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $imageName = basename($_FILES['image']['name']);
    $targetPath = "uploads/" . $imageName;
    move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
    $imagePath = $targetPath;
  }

  $sql = "INSERT INTO news_card (title, content, category, image_path) 
          VALUES ('$title', '$content', '$category', '$imagePath')";

  echo $conn->query($sql)
    ? "News added successfully."
    : "Error: " . $conn->error;
}

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
  <title>Add News</title>
  <script>
    function toggleCustomCategory(select) {
      const customInput = document.getElementById('custom-category-input');
      customInput.style.display = (select.value === 'other') ? 'block' : 'none';
    }
  </script>
</head>
<body>
  <h2>Add News</h2>
  <form method="POST" enctype="multipart/form-data">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="5" required></textarea><br><br>

    <label>Category:</label><br>
    <select name="category" onchange="toggleCustomCategory(this)" required>
      <option value="">Select Category</option>
      <option value="technology">Technology</option>
      <option value="sports">Sports</option>
      <option value="politics">Politics</option>
      <option value="entertainment">Entertainment</option>
      <option value="business">Business</option>
      <option value="health">Health</option>
      <option value="science">Science</option>
      <option value="world">World</option>
      <option value="education">Education</option>
      <option value="travel">Travel</option>
      <option value="environment">Environment</option>
      <option value="finance">Finance</option>
      <option value="fashion">Fashion</option>
      <option value="lifestyle">Lifestyle</option>
      <option value="food">Food</option>
      <option value="automotive">Automotive</option>
      <option value="culture">Culture</option>
      <option value="crime">Crime</option>
      <option value="weather">Weather</option>
      <option value="opinion">Opinion</option>
      <option value="other">Other</option>
    </select><br><br>

    <div id="custom-category-input" style="display: none;">
      <label>Specify Custom Category:</label><br>
      <input type="text" name="custom_category"><br><br>
    </div>

    <label>Image:</label><br>
    <input type="file" name="image"><br><br>

    <button type="submit">Submit</button>
  </form>
</body>
</html>
