<?php
// Set headers for JSON response
header('Content-Type: application/json');

require('../conn.php');

// Check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    if (isset($_GET['token'], $_GET['pin'])) {
        $auth = $_GET['token'];
        $switch = $_GET['pin'];

        // Retrieve the current value from the switches table
        $stmtCurrentValue = $conn->prepare('SELECT value FROM switches WHERE auth = :auth AND switch = :switch');
        $stmtCurrentValue->bindParam(':auth', $auth, PDO::PARAM_STR);
        $stmtCurrentValue->bindParam(':switch', $switch, PDO::PARAM_STR);
        $stmtCurrentValue->execute();
        $currentValue = $stmtCurrentValue->fetchColumn();

        if ($currentValue !== false) {
            // Calculate the new value
            $newValue = $currentValue == 0 ? 1 : 0;

            // Update the data in the switches table
            $stmtUpdateValue = $conn->prepare('UPDATE switches SET value = :value WHERE auth = :auth AND switch = :switch');
            $stmtUpdateValue->bindParam(':value', $newValue, PDO::PARAM_INT);
            $stmtUpdateValue->bindParam(':auth', $auth, PDO::PARAM_STR);
            $stmtUpdateValue->bindParam(':switch', $switch, PDO::PARAM_STR);

            try {
                $stmtUpdateValue->execute();
                $rowsAffected = $stmtUpdateValue->rowCount();
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
                    echo json_encode(array('error' => 'Token and pin combination not found or could not update the data.'));
                }
            } catch (PDOException $e) {
                // Handle database query error
                echo json_encode(array('error' => 'Failed to update data. Error'));
            }
        } else {
            echo json_encode(array('error' => 'Token and pin combination not found.'));
        }
    } else {
        echo json_encode(array('error' => 'Token or pin parameter is missing.'));
    }
} else {
    // Handle invalid request method
    echo json_encode(array('error' => 'Invalid request method.'));
}
?>
