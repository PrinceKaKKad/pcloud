<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcloud";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch unique actions from the log table and create a list based on first two words
$sql = "SELECT DISTINCT action FROM log";
$result = $conn->query($sql);

$commonActions = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $actionWords = explode(' ', $row['action']);
        $commonAction = implode(' ', array_slice($actionWords, 0, 2)); // First two words
        if (!in_array($commonAction, $commonActions)) {
            $commonActions[] = $commonAction;
        }
    }
}

// Close the MySQL connection
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Action Graphs</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Using Chart.js from CDN -->
    <style>
 .chart-container {
            padding: 20px;
            margin-top: 20px;
            border: 1px solid white;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 1);
            margin-bottom: 20px;
            width: 700px;
            height: 400px; /* Set a fixed height */
            flex: 1; /* Added */
            margin-right: 20px; /* Added */
        }

        .row {
            display: flex; /* Added */
            flex-wrap: wrap; /* Added */
            justify-content: space-between; /* Added */
        }
    </style>
    <?php include 'includes/header.php' ?>

</head>

<body>
    <div class="flex h-screen">
        <?php include 'includes/nav.php' ?>
        <main class = "flex-1 overflow-x-hidden overflow-y-auto">
            <div class="py-16 px-10">
                <h1 class="text-4xl text-black font-semibold mb-8">Templates</h1>
                <div class="container">
                    <?php
                    $count = 0;
                    foreach ($commonActions as $action) {
                        if ($count % 2 == 0) {
                            echo '<div class="row">'; // Start a new row
                        }
                        echo '<div class="col-lg-6">';
                        echo '<div class="chart-container">';
                        echo '<h2 class="text-center">Graph for Common Action: ' . $action . '</h2>';
                        echo '<canvas id="' . str_replace(' ', '', $action) . 'Chart"></canvas>';
                        echo '</div>';
                        echo '</div>';
                        $count++;
                        if ($count % 2 == 0 || $count == count($commonActions)) {
                            echo '</div>'; // End the row after every two graphs or at the end
                        }
                        // Query to retrieve data for the current action and calculate daily average for each user_id
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        $sql = "SELECT user_id, DATE(timestamp) AS action_date, COUNT(*) AS action_count
                                FROM log
                                WHERE action LIKE '%$action%'
                                GROUP BY user_id, action_date";
                        $result = $conn->query($sql);

                        $datasets = array();

                        while ($row = $result->fetch_assoc()) {
                            $user_id = $row['user_id'];

                            if (!isset($datasets[$user_id])) {
                                $datasets[$user_id] = array(
                                    'label' => 'User ' . $user_id,
                                    'data' => array(),
                                    'backgroundColor' => 'rgba(' . rand(0, 255) . ', ' . rand(0, 255) . ', ' . rand(0, 255) . ', 0.2)',
                                    'borderColor' => 'rgba(' . rand(0, 255) . ', ' . rand(0, 255) . ', ' . rand(0, 255) . ', 1)',
                                    'borderWidth' => 1,
                                    'lineTension' => 0 // Control the zigzag effect
                                );
                            }

                            $datasets[$user_id]['data'][] = array('x' => $row['action_date'], 'y' => $row['action_count']);
                        }

                        // Close the MySQL connection
                        $conn->close();
                        ?>

                        <script>
                            var ctx = document.getElementById('<?php echo str_replace(' ', '', $action); ?>Chart').getContext('2d');

                            var chartData = {
                                datasets: [
                                    <?php foreach ($datasets as $dataset) {
                                        echo '{
                                            label: "' . $dataset['label'] . '",
                                            data: ' . json_encode($dataset['data']) . ',
                                            backgroundColor: "' . $dataset['backgroundColor'] . '",
                                            borderColor: "' . $dataset['borderColor'] . '",
                                            borderWidth: ' . $dataset['borderWidth'] . ',
                                            lineTension: ' . $dataset['lineTension'] . '
                                        },';
                                    } ?>
                                ]
                            };

                            var chartOptions = {
                                responsive: true,
                                scales: {
                                    x: [{
                                        type: 'time',
                                        time: {
                                            unit: 'day',
                                            displayFormats: {
                                                day: 'YYYY-MM-DD'
                                            },
                                            tooltipFormat: 'YYYY-MM-DD'
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Date'
                                        }
                                    }],
                                    y: [{
                                        display: true,
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Action Frequency'
                                        }
                                    }]
                                }
                            };

                            var myChart = new Chart(ctx, {
                                type: 'line',
                                data: chartData,
                                options: chartOptions
                            });
                        </script>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>
    <?php include 'includes/footer.php' ?>

</body>

</html>
