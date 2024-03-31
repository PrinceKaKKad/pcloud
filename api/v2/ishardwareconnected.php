<?php

header('Content-Type: application/json');

require('conn.php');

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
                    echo json_encode(array('success' => 'Status updated successfully.'));
                } else {
                    echo json_encode(array('error' => 'No data found for the given auth.'));
                }
            } catch (PDOException $e) {
                // Handle database query error
                echo json_encode(array('error' => 'Failed to update status.'));
            }
        } else {
            $stmt = $conn->prepare('SELECT status,time FROM template WHERE auth = :auth');
            $stmt->bindParam(':auth', $auth, PDO::PARAM_STR);

            try {
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $status = $result['status'];
                    $time = strtotime($result['time']);
                    $currentTimestamp = time(); // Get the current timestamp
                    // echo ($currentTimestamp - $time);
                    if ($status === 'true' && ($currentTimestamp - $time) <= 30) {
                        echo $status;
                    }
                    else{
                        echo 'false';
                    }
                } else {
                    echo json_encode(array('error' => 'No data found for the given auth.'));
                }
            } catch (PDOException $e) {
                // Handle database query error
                echo json_encode(array('error' => 'Failed to retrieve status.'));
            }
        }
    } else {
        echo json_encode(array('error' => 'Auth parameter is missing.'));
    }
} else {
    // Handle invalid request method
    echo json_encode(array('error' => 'Invalid request method.'));
}

// Close the database connection
$conn = null;

?>
