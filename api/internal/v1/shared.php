<?php
require_once '../../conn.php';
header('Content-Type: application/json');

// Check if the request method is GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Get the user ID from the URL
    $user_id = $_GET['token'];

    // Fetch user details from the users table
    $stmt_user = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user_details = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user_details) {
        // Fetch templates associated with the user
        $stmt_template = $conn->prepare("SELECT id,name,auth FROM template WHERE user_id = ?");
        $stmt_template->execute([$user_id]);
        $templates = $stmt_template->fetchAll(PDO::FETCH_ASSOC);

        // Format the response with user details and templates
        $response = array(
            'uid' => $user_details['id'],
            'username' => $user_details['username'],
            'email' => $user_details['email'],
            'data' => $templates
        );

        // Send the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Handle user not found
        $response = array('error' => 'User not found.');

        // Send the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }
} else {
    // Handle invalid request method
    $response = array('error' => 'Invalid request method.');

    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
