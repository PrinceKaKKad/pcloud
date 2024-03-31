<?php

// Set headers for JSON response
header('Content-Type: application/json');

require('../conn.php');

// Check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    if (isset($_GET['token'])) {
        $auth = $_GET['token'];

        if (isset($_GET['update'])) {
            $update = $_GET['update'];

            // Update the database status and time
            $stmt = $conn->prepare('UPDATE template SET status = :status, time = NOW() WHERE auth = :auth');
            $stmt->bindParam(':status', $update, PDO::PARAM_STR);
            $stmt->bindParam(':auth', $auth, PDO::PARAM_STR);

            try {
                $stmt->execute();
                $rowsAffected = $stmt->rowCount();
                if ($rowsAffected > 0) {
                    // echo json_encode(array('success' => 'Status updated successfully.'));
                } else {
                    echo json_encode(array('error' => 'No data found for the given auth.'));
                }
            } catch (PDOException $e) {
                // Handle database query error
                echo json_encode(array('error' => 'Failed to update status.'));
            }
        }

        // Retrieve all the pins' output for the provided auth
        $stmt = $conn->prepare('SELECT id, auth, switch, value FROM switches WHERE auth = :auth');
        $stmt->bindParam(':auth', $auth, PDO::PARAM_STR);

        try {
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($results)) {
                $output = array(
                    'id' => $results[0]['id'],
                    'auth' => $results[0]['auth'],
                    'data' => array()
                );
                foreach ($results as $row) {
                    $output['data'][$row['switch']] = $row['value'];
                }
                echo json_encode($output);
            } else {
                echo json_encode(array('error' => 'No data found.'));
            }
        } catch (PDOException $e) {
            // Handle database query error
            echo json_encode(array('error' => 'Failed to retrieve data.'));
        }
    } else {
        echo json_encode(array('error' => 'Token parameter is missing.'));
    }
} else {
    // Handle invalid request method
    echo json_encode(array('error' => 'Invalid request method.'));
}
?>
