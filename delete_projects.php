<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
  header("Location: login");
  exit();
}

include 'includes/conn.php';

$logQuery = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
$logStmt = $pdo->prepare($logQuery);

if (isset($_GET['token']) && isset($_GET['auth'])) {
  $id = $_GET['token'];
  $temp_id = $_GET['id'];
  $auth = $_GET['auth'];

  // Retrieve the template from the database
  $query = "SELECT * FROM template WHERE id=:id";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':id', $temp_id);
  $stmt->execute();
  $template = $stmt->fetch();

  try {
    $stmt = $pdo->prepare("DELETE FROM template WHERE id=:id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM switches WHERE temp_id=:temp_id AND auth=:auth");
    $stmt->bindValue(':temp_id', $temp_id);
    $stmt->bindValue(':auth', $auth);
    $stmt->execute();

    // Log the action
    $logStmt->bindValue(':user_id', $_SESSION['user_id']);
    $logStmt->bindValue(':username', $_SESSION['username']);
    $logStmt->bindValue(':action', 'Deleted Template: ' . $template['name']);
    $logStmt->bindValue(':timestamp', date('Y-m-d H:i:s'));
    $logStmt->execute();

    // Handle successful deletion
    echo "Records deleted successfully.";
  } catch (PDOException $e) {
    // Handle database errors
    echo "An error occurred: " . $e->getMessage();
  }
} else {
  // Handle missing parameters
  echo "Missing ID or auth parameter.";
}

// Redirect to desired location
header("Location: index");
exit();
?>