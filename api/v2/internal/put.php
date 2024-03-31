<?php

// Set headers for JSON response
header('Content-Type: application/json');

require('../../conn.php');

// Check request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'PUT') {
    if (isset($_GET['token'], $_GET['pin'], $_GET['value'])) {
        $auth = $_GET['token'];
        $switch = $_GET['pin'];
        $value = $_GET['value'];
        $data = 0;

        // Update the data in the switches table
        $stmtSwitches = $conn->prepare('UPDATE switches SET value = :value WHERE auth = :auth AND switch = :switch');
        $stmtSwitches->bindParam(':value', $value, PDO::PARAM_STR);
        $stmtSwitches->bindParam(':auth', $auth, PDO::PARAM_STR);
        $stmtSwitches->bindParam(':switch', $switch, PDO::PARAM_STR);

        // Check if the value is 0
        if ($value == '0') {
            // If value is 0, set data to 0
            $data = '0';
        }

        // Update the data in the dynamic_switches table
        $stmtDynamicSwitches = $conn->prepare('UPDATE dynamic_switches SET value = :value, data = :data, timestamp = NOW() WHERE auth = :auth AND switch = :switch');
        $stmtDynamicSwitches->bindParam(':value', $value, PDO::PARAM_STR);
        $stmtDynamicSwitches->bindParam(':data', $data, PDO::PARAM_STR);
        $stmtDynamicSwitches->bindParam(':auth', $auth, PDO::PARAM_STR);
        $stmtDynamicSwitches->bindParam(':switch', $switch, PDO::PARAM_STR);
        try {
            // Try to update in the switches table
            $stmtSwitches->execute();
            $rowsAffectedSwitches = $stmtSwitches->rowCount();

            // If not found in switches, try updating in the dynamic_switches table
            if ($rowsAffectedSwitches === 0) {
                $stmtDynamicSwitches->execute();
                $rowsAffectedDynamicSwitches = $stmtDynamicSwitches->rowCount();

                if ($rowsAffectedDynamicSwitches > 0) {
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
                    echo json_encode(array('error' => 'Pin not found in both switches and dynamic_switches.'));
                }
            } else {
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
