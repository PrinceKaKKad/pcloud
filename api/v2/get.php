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

        // Check in switches table
        $stmtSwitches = $conn->prepare('SELECT value FROM switches WHERE auth = :auth AND switch = :switch');
        $stmtSwitches->bindParam(':auth', $auth, PDO::PARAM_STR);
        $stmtSwitches->bindParam(':switch', $switch, PDO::PARAM_STR);

        // Check in dynamic_switches table
        $stmtDynamicSwitches = $conn->prepare('SELECT value, data FROM dynamic_switches WHERE auth = :auth AND switch = :switch');
        $stmtDynamicSwitches->bindParam(':auth', $auth, PDO::PARAM_STR);
        $stmtDynamicSwitches->bindParam(':switch', $switch, PDO::PARAM_STR);

        try {
            // Execute the switches query
            $stmtSwitches->execute();
            $resultSwitches = $stmtSwitches->fetchAll(PDO::FETCH_ASSOC);

            // Execute the dynamic_switches query
            $stmtDynamicSwitches->execute();
            $resultDynamicSwitches = $stmtDynamicSwitches->fetchAll(PDO::FETCH_ASSOC);

            // Check which table has the data and return the appropriate response
            if (!empty($resultSwitches)) {
                echo json_encode(array($switch => array('value' => $resultSwitches[0]['value'])));
            } elseif (!empty($resultDynamicSwitches)) {
                echo json_encode(array($switch => array('value' => $resultDynamicSwitches[0]['value'], 'data' => $resultDynamicSwitches[0]['data'])));
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
?>
