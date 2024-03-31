<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_username'])) {
  header("Location: login");
  exit();
}
include 'includes/conn.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $pdo->prepare("DELETE FROM users WHERE id=:id");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  
  $id = $_GET['id'];
  $stmt = $pdo->prepare("DELETE FROM template WHERE user_id=:id");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
}

header("Location: user.php");
exit();
?>
