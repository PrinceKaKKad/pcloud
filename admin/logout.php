<?php
// Start the session
session_start();

unset($_SESSION['admin_username']);

// Redirect to the login page
header("Location: login");
exit();
?>
