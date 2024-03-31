<?php
// Start the session
session_start();
unset($_SESSION['username']);
unset($_SESSION['user_id']);
setcookie('__pcloud_login_token', '', time() - 1, '/');
// Redirect to the login page
header("Location: login");
exit();
?>
