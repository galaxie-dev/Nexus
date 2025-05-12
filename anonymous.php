<?php
session_start();
if (isset($_SESSION['user_id'])) {
  $_SESSION['anonymous'] = true;
  $_SESSION['original_user'] = $_SESSION['user_id'];
  unset($_SESSION['user_id']);
}
header("Location: index.php");
?>
