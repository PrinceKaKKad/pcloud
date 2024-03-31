<?php 
// Database credentials
$db_host = 'localhost';
$db_name = 'pcloud';
$db_user = 'root';
$db_pass = '';

// Connect to the database using PDO
try {
    $conn = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle database connection error
    echo json_encode(array('error' => 'There is error at server side please try after sometime.'));
    exit;
}
?>