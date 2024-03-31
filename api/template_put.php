<?php

// Set headers for JSON response
header('Content-Type: application/json');

require('conn.php');

// Check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    if (isset($_GET['token'], $_GET['pin'], $_GET['value'])) {
        $auth = $_GET['token'];
        $switch = $_GET['pin'];
        $value = $_GET['value'];

        $stmt = $conn->prepare('UPDATE switches SET value = :value WHERE auth = :auth AND switch = :switch');
        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
        $stmt->bindParam(':auth', $auth, PDO::PARAM_STR);
        $stmt->bindParam(':switch', $switch, PDO::PARAM_STR);

        try {
            $stmt->execute();
		    $rowsAffected = $stmt->rowCount();
		    if ($rowsAffected > 0) {
		        echo json_encode(array('success' => 'Data updated successfully.'));
                header("Location: {$_SERVER['HTTP_REFERER']}");
		    } else {
		        echo json_encode(array('error' => 'No data found for the given auth and switch.'));
		    }
        } catch (PDOException $e) {
            // Handle database query error
            echo json_encode(array('error' => 'Failed to update data.'));
            header("Location: {$_SERVER['HTTP_REFERER']}");
        }
    } else {
        echo json_encode(array('error' => 'Auth, switch, or value parameter is missing.'));
    }
} else {
    // Handle invalid request method
    echo json_encode(array('error' => 'Invalid request method.'));
    header("Location: {$_SERVER['HTTP_REFERER']}");
}
