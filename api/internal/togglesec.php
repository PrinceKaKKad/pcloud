<?php
// Set headers for JSON response
header('Content-Type: application/json');

require('../conn.php');

// Check request method
// Check request method
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
            if ($currentValue == 0) {
                // Set value to 1 for 1 second
                $newValue = 1;
                $stmtUpdateValue = $conn->prepare('UPDATE switches SET value = :value WHERE auth = :auth AND switch = :switch');
                $stmtUpdateValue->bindParam(':value', $newValue, PDO::PARAM_INT);
                $stmtUpdateValue->bindParam(':auth', $auth, PDO::PARAM_STR);
                $stmtUpdateValue->bindParam(':switch', $switch, PDO::PARAM_STR);
                $stmtUpdateValue->execute();

                // Delay for 3 second
                sleep(3);

                // Set value back to 0
                $newValue = 0;
                $stmtUpdateValue->execute();
            } else {
                // Delay for 1 second
                sleep(1);

                // Set value to 0
                $newValue = 0;
                $stmtUpdateValue = $conn->prepare('UPDATE switches SET value = :value WHERE auth = :auth AND switch = :switch');
                $stmtUpdateValue->bindParam(':value', $newValue, PDO::PARAM_INT);
                $stmtUpdateValue->bindParam(':auth', $auth, PDO::PARAM_STR);
                $stmtUpdateValue->bindParam(':switch', $switch, PDO::PARAM_STR);
                $stmtUpdateValue->execute();
            }

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
