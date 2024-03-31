<?php

// Set headers for JSON response
header('Content-Type: application/json');

require('../conn.php');

// Check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'PUT') {
    if (isset($_GET['token'], $_GET['pin'], $_GET['value'])) {
        $auth = $_GET['token'];
        $switch = $_GET['pin'];
        $value = $_GET['value'];

        // Update the data in the switches table
        $stmt = $conn->prepare('UPDATE switches SET value = :value WHERE auth = :auth AND switch = :switch');
        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
        $stmt->bindParam(':auth', $auth, PDO::PARAM_STR);
        $stmt->bindParam(':switch', $switch, PDO::PARAM_STR);

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
                echo json_encode(array('error' => 'You can not put same data again or Please check if the provided Token and Pin are correct.'));
            }
        } catch (PDOException $e) {
            // Handle database query error
            echo json_encode(array('error' => 'Failed to update data. Error'));
        }
    } else {
        echo json_encode(array('error' => 'Token, pin, or value parameter is missing.'));
    }
} else {
    // Handle invalid request method
    echo json_encode(array('error' => 'Invalid request method.'));
}
