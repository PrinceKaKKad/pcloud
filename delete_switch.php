<?php
session_start();
require_once('includes/conn.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit();
}

// Include the logging code here
$logQuery = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
$logStmt = $pdo->prepare($logQuery);

// Check if switch ID is specified in the URL
if (!isset($_GET['token'])) {
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}

// Get the switch ID from the URL
$switchId = $_GET['token'];

// Retrieve the switch from the database
$query = "SELECT s.*, t.user_id
          FROM switches AS s
          INNER JOIN template AS t ON s.temp_id = t.id
          WHERE s.id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $switchId);
$stmt->execute();
$switch = $stmt->fetch();

if (!$switch) {
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}

// Check if the logged-in user is authorized to delete the switch
if ($switch['user_id'] != $_SESSION['user_id']) {
    // Redirect to an error page or show a message
    echo '<script>alert("Please login from your account.");</script>';
    // Redirect back to the previous page
    header("Location: shared");
    exit();
}

// Delete the switch from the database
$querySwitches = "DELETE FROM switches WHERE id=:id";
$stmtSwitches = $pdo->prepare($querySwitches);
$stmtSwitches->bindParam(':id', $switchId);
$stmtSwitches->execute();

// Check if the switch exists in dynamic_switches and delete it if found
$queryDynamicSwitches = "DELETE FROM dynamic_switches WHERE switch=:switch";
$stmtDynamicSwitches = $pdo->prepare($queryDynamicSwitches);
$stmtDynamicSwitches->bindParam(':switch', $switch['switch']);
$stmtDynamicSwitches->execute();

// Log the action
$logStmt->bindValue(':user_id', $_SESSION['user_id']);
$logStmt->bindValue(':username', $_SESSION['username']);
$logStmt->bindValue(':action', 'Deleted Switch: ' . $switch['switch'] . ' From Template: ' . $switch['name']);
$logStmt->bindValue(':timestamp', date('Y-m-d H:i:s'));
$logStmt->execute();

// Redirect back to the previous page
header("Location: ".$_SERVER['HTTP_REFERER']);
exit();
?>
