<?php

// Set headers for JSON response
header('Content-Type: application/json');

require('../conn.php');

// Check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    if (isset($_GET['token'])) {
        $auth = $_GET['token'];

        // Update the data in the switches table
        $stmt = $conn->prepare('UPDATE switches SET value = CASE WHEN value = 0 THEN 1 ELSE 0 END WHERE auth = :auth');
        $stmt->bindParam(':auth', $auth, PDO::PARAM_STR);

        try {
            $stmt->execute();
            $rowsAffected = $stmt->rowCount();
            if ($rowsAffected > 0) {
                // Retrieve the template name associated with the updated data
                $stmtTemplate = $conn->prepare('SELECT name FROM template WHERE auth = :auth');
                $stmtTemplate->bindParam(':auth', $auth, PDO::PARAM_STR);
                $stmtTemplate->execute();
                $templateName = $stmtTemplate->fetchColumn();

                if ($templateName) {
                    echo json_encode(array('success' => 'Data updated successfully, Template Name: ' . $templateName));
                } else {
                    echo json_encode(array('success' => 'Data updated successfully.', 'template_name' => 'Unknown'));
                }
            } else {  
                echo json_encode(array('error' => 'Token not found or could not update the data.'));
            }
        } catch (PDOException $e) {
            // Handle database query error
            echo json_encode(array('error' => 'Failed to update data. Error'));
        }
    } else {
        echo json_encode(array('error' => 'Token parameter is missing.'));
    }
} else {
    // Handle invalid request method
    echo json_encode(array('error' => 'Invalid request method.'));
}

?>
