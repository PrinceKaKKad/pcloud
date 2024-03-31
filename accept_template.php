<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
  echo '<script>alert("You need to login first!");</script>';
  header("Location: login");
  exit();
}

include 'includes/conn.php';

// Include the logging code here
$logQuery = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
$logStmt = $pdo->prepare($logQuery);

// Check if token is specified in the URL
if (!isset($_GET['token'])) {
  // header("Location: error.php");
  exit();
}

// Get the token from the URL
$token = $_GET['token'];

// Check if the token exists in the shared_template table
$query = "SELECT * FROM shared_template WHERE token = :token";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':token', $token);
$stmt->execute();
$template = $stmt->fetch();

if (!$template) {
  echo "Invalid token. Please check the URL or contact the sender.";
} else {
  // Update the status to 1 (verified)
  $query = "UPDATE shared_template SET status = 1 WHERE token = :token";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':token', $token);
  $stmt->execute();

  // Log the action
  $logStmt->bindValue(':user_id', 'NULL');
  $logStmt->bindValue(':username', 'User');
  $logStmt->bindValue(':action', 'verified The template invitation.');
  $logStmt->bindValue(':timestamp', date('Y-m-d H:i:s'));
  $logStmt->execute();

  echo '<script>alert("Template verified successfully. You can now access the template.");</script>';
  echo '<script>window.close();</script>';
}
?>