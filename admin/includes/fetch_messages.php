<?php
session_start();
require_once 'conn.php';

if (!isset($_SESSION['admin_username'])) {
    exit('Unauthorized access');
}

if (isset($_GET['ticket_number']) && isset($_GET['last_message_id'])) {
    $ticket_number = $_GET['ticket_number'];
    $last_message_id = $_GET['last_message_id'];

    $stmt = $pdo->prepare("SELECT * FROM support_messages WHERE ticket_number = ? AND id > ?");
    $stmt->execute([$ticket_number, $last_message_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($messages);
} else {
    exit('Invalid request');
}
?>
