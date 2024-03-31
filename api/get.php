<?php

// Set headers for JSON response
header('Content-Type: application/json');

require('conn.php');

// Check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    if (isset($_GET['token'], $_GET['pin'])) {
        $auth = $_GET['token'];
        $switch = $_GET['pin'];

        $stmt = $conn->prepare('SELECT value FROM switches WHERE auth = :auth AND switch = :switch');
        $stmt->bindParam(':auth', $auth, PDO::PARAM_STR);
        $stmt->bindParam(':switch', $switch, PDO::PARAM_STR);

        try {
            $stmt->execute();
		    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		    if (!empty($result)) {
		        echo $result[0]['value'];
		    } else {
		        echo json_encode(array('error' => 'No data found.'));
		    }
        } catch (PDOException $e) {
            // Handle database query error
            echo json_encode(array('error' => 'Failed to retrieve data.'));
        }
    } else {
        echo json_encode(array('error' => 'Auth or switch parameter is missing.'));
    }
} else {
    // Handle invalid request method
    echo json_encode(array('error' => 'Invalid request method.'));
}
