<?php
session_start();
require_once 'conn.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(403); // Forbidden
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    exit();
}

// Check if ticket number is provided
if (!isset($_GET['ticket_number']) || empty(trim($_GET['ticket_number']))) {
    http_response_code(400); // Bad Request
    exit();
}

$ticket_number = trim($_GET['ticket_number']);

// Get the last message ID
$last_message_id = isset($_GET['last_message_id']) ? intval($_GET['last_message_id']) : 0;

// Fetch new messages
$stmt = $pdo->prepare("SELECT * FROM support_messages WHERE ticket_number = ? AND id > ? ORDER BY id ASC");
$stmt->execute([$ticket_number, $last_message_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return new messages as JSON
header('Content-Type: application/json');
echo json_encode($messages);
