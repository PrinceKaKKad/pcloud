<?php
// Include the database connection
require_once 'includes/conn.php';

// Get the verification string from the URL
$verificationString = $_GET['verify'];

$logQuery = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
$logStmt = $pdo->prepare($logQuery);

// Search for the verification string in the database
$stmt = $pdo->prepare("SELECT * FROM users WHERE verify = ?");
$stmt->execute([$verificationString]);
$user = $stmt->fetch();

if ($user) {
  // Update the status of the user to 1 (verified)
  $stmt = $pdo->prepare("UPDATE users SET status = 1, verify = ? WHERE id = ?");
  
  // Generate a new verification string
  $newVerificationString = generateRandomString(16);
  
  $stmt->execute([$newVerificationString, $user['id']]);

  $logStmt->bindValue(':user_id', $user['id']);
    $logStmt->bindValue(':username', $user['username']);
    $logStmt->bindValue(':action', 'Just Verified account: ' . $user['username']);
    $logStmt->bindValue(':timestamp', date('Y-m-d H:i:s'));
    $logStmt->execute();

  echo "<script>
    if (confirm('Email Verified!')) {
        window.close();
    }
  </script>";

} else {
  echo "Invalid verification link.";
}

// Function to generate a random string
function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>