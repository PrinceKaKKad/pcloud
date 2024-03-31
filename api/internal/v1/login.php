<?php
require_once '../../conn.php';

// Check request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Retrieve the username, password, and action from the request URL
    $username = $_GET['username'];
    $password = $_GET['password'];

    // Check if the application parameter is available
    if (isset($_GET['application'])) {
        $application = $_GET['application'];
    } else {
        // Handle missing application parameter
        $response = array(
            'status' => '0',
            'error' => 'Missing application parameter.');

        // Send the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Prepare and execute the SQL query
    $query = "SELECT id, username, email, password, status FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->execute(array(':username' => $username));

    // Check if a matching user is found
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $storedPassword = $row['password'];
        if (password_verify($password, $storedPassword)) {
            // Insert log entry
            
            if ($row['status'] == 1) {
                    $logTimestamp = date('Y-m-d H:i:s');
                    $logQuery = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
                    $logStmt = $conn->prepare($logQuery);
                    $logStmt->bindValue(':user_id', $row['id']);
                    $logStmt->bindValue(':username', $row['username']);
                    $logStmt->bindValue(':action', "User ".$row['username']." has logged in via $application.");
                    $logStmt->bindValue(':timestamp', $logTimestamp);
                    $logStmt->execute();
                    $response = array(
                        'status' => "1",
                        'user_id' => (string) $row['id'],
                        'username' => $row['username'],
                        'email' => $row['email']
                    );
            }
            else{
                    $response = array('status' => "0",
                                      'error' => "account not verified"
                    );
                }
        } else {
            $response = array('status' => "0");
        }
    } else {
        $response = array('status' => "0");
    }

    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Handle invalid request method
    $response = array('error' => 'Invalid request method.');

    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
