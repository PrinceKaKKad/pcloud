<?php
session_start();
require_once('includes/conn.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit();
}

// Check if template ID is specified in the URL
if (!isset($_GET['token'])) {
    header("Location: index");
    exit();
}

$tempId = $_GET['token'];
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM template WHERE id=:id AND user_id=:user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $tempId);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$template = $stmt->fetch();

if (!$template) {
    header("Location: index");
    exit();
}

$auth = $template['auth'];

// Handle form submission for adding a new switch
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $switch = $_POST['switch'];
    $value = $_POST['value'];
    $switchType = $_POST['switch-type'];

    // Check if the switch value already exists in switches table
    $querySwitches = "SELECT * FROM switches WHERE temp_id=:temp_id AND switch=:switch";
    $stmtSwitches = $pdo->prepare($querySwitches);
    $stmtSwitches->bindParam(':temp_id', $tempId);
    $stmtSwitches->bindParam(':switch', $switch);
    $stmtSwitches->execute();
    $existingSwitchInSwitches = $stmtSwitches->fetch();

    // Check if the switch value already exists in dynamic_switches table
    $queryDynamicSwitches = "SELECT * FROM dynamic_switches WHERE temp_id=:temp_id AND switch=:switch";
    $stmtDynamicSwitches = $pdo->prepare($queryDynamicSwitches);
    $stmtDynamicSwitches->bindParam(':temp_id', $tempId);
    $stmtDynamicSwitches->bindParam(':switch', $switch);
    $stmtDynamicSwitches->execute();
    $existingSwitchInDynamicSwitches = $stmtDynamicSwitches->fetch();

    if ($existingSwitchInSwitches || $existingSwitchInDynamicSwitches) {
        // Switch value already exists, display an error message
        echo "<script>alert('Switch value already exists for this template. Please use a different one.')</script>";
    } else {
        if ($switchType === 'dynamic') {
            $datapin = $_POST['datapin'];

            // Insert the new switch into the dynamic_switches table
            $query = "INSERT INTO dynamic_switches (temp_id, auth, name, switch, value, datapin) VALUES (:temp_id, :auth, :name, :switch, :value, :datapin)";
        } else {
            // Insert the new switch into the switches table
            $query = "INSERT INTO switches (temp_id, auth, name, switch, value) VALUES (:temp_id, :auth, :name, :switch, :value)";
        }

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':temp_id', $tempId);
        $stmt->bindParam(':auth', $auth);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':switch', $switch);
        $stmt->bindParam(':value', $value);

        if ($switchType === 'dynamic') {
            $stmt->bindParam(':datapin', $datapin);
        }

        $stmt->execute();

        // Log the action
        $logQuery = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
        $logStmt = $pdo->prepare($logQuery);
        $logStmt->bindValue(':user_id', $user_id);
        $logStmt->bindValue(':username', $_SESSION['username']);
        $logStmt->bindValue(':action', 'Added switch: ' . $name . ' to template: ' . $template['name']);
        $logStmt->bindValue(':timestamp', date('Y-m-d H:i:s'));
        $logStmt->execute();
    }
}

// Retrieve all switches for this template from both switches and dynamic_switches tables
$queryAllSwitches = "
    SELECT * FROM (
        SELECT temp_id, auth, name, switch, value, NULL as datapin FROM switches WHERE temp_id=:temp_id
        UNION ALL
        SELECT temp_id, auth, name, switch, value, datapin FROM dynamic_switches WHERE temp_id=:temp_id
    ) AS all_switches";
$stmtAllSwitches = $pdo->prepare($queryAllSwitches);
$stmtAllSwitches->bindParam(':temp_id', $tempId);
$stmtAllSwitches->execute();
$allSwitches = $stmtAllSwitches->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>pCloud | Projects</title>
  <?php include 'includes/header.php' ?>
  <link href="assets/css/switch.css" rel="stylesheet" />
</head>

<body>
  <div class="flex h-screen">
    <?php include 'includes/nav.php' ?>
    <main class="flex-1 overflow-x-hidden overflow-y-auto">
      <div class="py-16 px-10">
        <h1 class="text-4xl text-black font-semibold mb-8">Projects</h1>
        <div class="bg-gray-100 rounded-lg p-6">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-gray-200 p-3 rounded">
              <li class="breadcrumb-item text-gray-400" aria-current="page"><a href="index">Dashboard</a></li>
              <li class="breadcrumb-item text-gray-600 active"><?php echo $template['name'] ?> Project</li>
            </ol>
          </nav>

          <div class="flex justify-center mt-6">
            <div class="card bg-white rounded-lg shadow-md p-6">
              <h5 class="text-2xl font-bold mb-4 overflow-hidden"><?php echo $template['name'] ?> Project</h5>
              <div id="status"></div>

              <?php include 'assets/js/hardwarestatus.php'; ?>

              <p class="text-gray-700" title="Auth Code" onclick="copyToClipboard('<?php echo $auth ?>')">
                <b>Auth Token:</b> <?php echo $auth ?> <i id="copy" class="fa fa-copy"></i>
              </p>

              <div class="flex flex-wrap space-x-4 mt-6">
                <a type="button" onclick="toggleEdit()" id="edit-button" class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600">
                  Edit Template
                </a>
                <a href="invite_template?token=<?php echo $tempId .'&tname='. $template['name'] ?>" class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600">
                  Invite User
                </a>
                <button type="button" id="add-switch-button" class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600">
                  Add New Switch
                </button>
                <button type="button" id="download-button" class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600">
                  Download Arduino Code
                </button>
              </div>
            </div>
          </div>

          <div class="switch-data mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
<?php
usort($allSwitches, function ($a, $b) {
    return strcmp($a['switch'], $b['switch']);
});

$i = 1;
if (empty($allSwitches)) { // Corrected variable name
?>
    <div class="col-span-full">
      <div class="card bg-white rounded-lg shadow-md p-6">
        <h5 class="text-gray-700 d-flex justify-content-center">No switches available.</h5>
      </div>
    </div>
    <?php } else {

foreach ($allSwitches as $switch) :
    ?>
    <?php if (!isset($switch['datapin']) || $switch['datapin'] === null) : // Corrected condition ?>

    <div class="col-span-1">
      <div class="card bg-white rounded-lg shadow-md p-6">
        <h5 class="text-gray-700"><?php echo $switch['name'] ?></h5>
        <h6 class="text-gray-500 mb-3">Switch: <?php echo $switch['switch'] ?></h6>
        <label class="text-gray-700" id="status-<?php echo $switch['switch'] ?>">
          Fetching....
        </label>
        <div class="switch">
          <input type="checkbox" class="toggle-action" id="toggle-<?php echo $switch['switch'] ?>" data-switch="<?php echo $switch['switch'] ?>" /><label for="toggle-<?php echo $switch['switch'] ?>"></label>
        </div><br>
        <a href="delete_switch.php?token=<?php echo $switch['switch'] ?>" style="display: none;" class="delete-link btn btn-danger bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-red-600" title="Delete" id="delete-link-<?php echo $switch['switch'] ?>" onclick="return confirm('Are you sure you want to delete this switch?')">Delete</a>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($switch['datapin']) && $switch['datapin'] !== null) : // Corrected condition ?>
    <div class="col-span-1">
      <div class="card bg-white rounded-lg shadow-md p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Display additional details for dynamic switches -->
        <div class="col-span-1">
          <h5 class="text-gray-700"><?php echo $switch['name'] ?></h5>
          <h6 class="text-gray-500 mb-3">Switch: <?php echo $switch['switch'] ?></h6>
          <label class="text-gray-700" id="status-<?php echo $switch['switch'] ?>">
            Fetching....
          </label>
          <div class="switch">
            <input type="checkbox" class="toggle-action" id="toggle-<?php echo $switch['switch'] ?>" data-switch="<?php echo $switch['switch'] ?>" /><label for="toggle-<?php echo $switch['switch'] ?>"></label>
          </div><br>
          <a href="delete_switch.php?token=<?php echo $switch['switch'] ?>" style="display: none;" class="delete-link btn btn-danger bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-red-600" title="Delete" id="delete-link-<?php echo $switch['switch'] ?>" onclick="return confirm('Are you sure you want to delete this switch?')">Delete</a>
        </div>

        <div class="col-span-1">
          <div class="gauge gauge-<?php echo $switch['switch'] ?>">
            <div class="gauge__body">
              <div class="gauge__fill gauge-fill-<?php echo $switch['switch'] ?>" id="<?php echo $switch['switch'] ?>"></div>
              <div class="gauge__cover gauge-cover-<?php echo $switch['switch'] ?>" id="<?php echo $switch['switch'] ?>"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php $i++;
    endforeach;
} ?>


          </div>

          <div id="add-switch-form" tabindex="-1" class="bg-gray-200  hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 rounded-lg shadow-md w-full md:max-w-md">
            <div class="relative p-4 w-full">
              <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center bg-gray-400 justify-between p-4 md:p-5 border-b rounded-t">
                  <h3 class="text-lg font-semibold text-gray-900">
                    Add New Switch
                  </h3>
                  <button type="button" id="close-switch-form-button" class="text-gray-400  bg-gray-600 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="add-switch-form">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                  </button>
                </div>
                <form class="p-4 md:p-5" method="post" action="">
                  <div class="form-group">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Switch name:</label>
                    <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" id="name" name="name" placeholder="Fan, Light" required class="form-input">
                  </div>
                  <div class="form-group">
                    <label for="switch-type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Switch Type:</label>
                    <div class="flex items-center space-x-4">
                      <input type="radio" id="non-dynamic" name="switch-type" value="non-dynamic" checked>
                      <label for="non-dynamic" class="text-sm">Non-Dynamic</label>

                      <input type="radio" id="dynamic" name="switch-type" value="dynamic">
                      <label for="dynamic" class="text-sm">Dynamic</label>
                    </div>
                  </div>

                  <div class="form-group" id="dynamic-switch-options" style="display:none;">
                    <label for="datapin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Data Pin:</label>
                    <select name="datapin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" id="datapin" class="select form-select">
                      <option value="A0">A0</option>
                      <option value="A1">A1</option>
                      <option value="A2">A2</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="switch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PIN:</label>
                    <select name="switch" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" id="switch" class="select form-select" required>

                      //Non-usable pins
                      <option value="" disabled>Reserved Pins</option>
                      <option value="D0" disabled>D0 - Reserved</option>
                      <option value="D1" disabled>D1 - Reserved</option>
                      <option value="D2" disabled>D2 - Reserved</option>
                      <option value="D3" disabled>D3 - Reserved</option>
                      <option value="" disabled></option>

                      //Usable Pin
                      <option value="" disabled>Non-Reserved Pins</option>
                      <option value="D4">D4</option>
                      <option value="D5">D5</option>
                      <option value="D6">D6</option>
                      <option value="D7">D7</option>
                      <option value="D8">D8</option>
                      <option value="" disabled></option>

                      //Virtual Pins
                      <option value="" disabled>Virtual Pins</option>
                      <option value="V0">V0</option>
                      <option value="V1">V1</option>
                      <option value="V2">V2</option>
                      <option value="V3">V3</option>
                      <option value="V4">V4</option>
                      <option value="V5">V5</option>
                      <option value="V6">V6</option>
                      <option value="V7">V7</option>
                      <option value="V8">V8</option>
                      <option value="V0">V9</option>
                      <option value="V1">V10</option>
                      <option value="V2">V11</option>
                      <option value="V3">V12</option>
                      <option value="V4">V13</option>
                      <option value="V5">V14</option>
                      <option value="V6">V15</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="value" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Value:</label>
                    <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" id="value" name="value" value="0" placeholder="Custom data or 0 for off and 1 for on" required class="form-input">
                  </div>
                  <div class="form-group"><br>
                    <input type="submit" name="submit" class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600" value="+ Add switch" style="cursor: pointer;">
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div id="download-form" tabindex="-1" class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-200 p-6 rounded-lg shadow-md w-full md:max-w-md">
            <div class="relative p-4 w-full">
              <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center bg-gray-400 justify-between p-4 md:p-5 border-b rounded-t">
                  <h3 class="text-lg font-semibold text-gray-900">
                    Enter your WIFI credentials
                  </h3>
                  <button id="close-download-form-button" type="button" id="close-switch-form-button" class="text-gray-400  bg-gray-600 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="add-switch-form">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                  </button>
                </div>
                <form action="templates/switch.php" class="p-4 md:p-5" method="POST" onsubmit="hideDownloadForm()">
                  <label for="ssid" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">SSID:</label>
                  <input type="text" name="ssid" id="ssid" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">

                  <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password:</label>
                  <input type="password" name="password" id="password" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">

                  <input type="hidden" name="authCode" id="authCode" value="<?php echo $auth ?>" required>
                  <input type="hidden" name="templateName" id="templateName" value="<?php echo $template['name'] ?>" required>
                  <br>
                  <input class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600" type="submit" value="Download">
                </form>
              </div>
            </div>
          </div>

        </div>
      </div>

      <script>
        function toggleEdit() {
          var editButton = document.getElementById('edit-button');
          if (editButton.textContent === 'Edit Template') {
            editButton.textContent = 'Cancel Edit';
            toggleElements('toggle-action', 'none');
            toggleElements('delete-link', 'inline-block');
          } else {
            editButton.textContent = 'Edit Template';
            toggleElements('toggle-action', 'inline-block');
            toggleElements('delete-link', 'none');
          }
        }

        function toggleElements(className, displayValue) {
          var elements = document.getElementsByClassName(className);
          for (var i = 0; i < elements.length; i++) {
            elements[i].style.display = displayValue;
          }
        }
      </script>

      <?php include 'assets/js/buttons.php'; ?>
    </main>
  </div>

  <script>
    const addSwitchButton = document.querySelector('#add-switch-button');
    const addSwitchForm = document.querySelector('#add-switch-form');
    const closeSwitchFormButton = document.querySelector('#close-switch-form-button');
    addSwitchButton.addEventListener('click', function() {
      addSwitchForm.style.display = 'block';
    });
    closeSwitchFormButton.addEventListener('click', function() {
      addSwitchForm.style.display = 'none';
    });
    const downloadButton = document.querySelector('#download-button');
    const downloadForm = document.querySelector('#download-form');
    const closedownloadFormButton = document.querySelector('#close-download-form-button');
    downloadButton.addEventListener('click', function() {
      downloadForm.style.display = 'block';
    });
    closedownloadFormButton.addEventListener('click', function() {
      downloadForm.style.display = 'none';
    });

    function hideDownloadForm() {
      document.getElementById('download-form').style.display = 'none';
    }

    function copyToClipboard(text) {
      // Create a temporary input element
      var tempInput = document.createElement("input");
      tempInput.value = text;
      document.body.appendChild(tempInput);
      copy.classList.remove("fa-copy");
      copy.classList.add("fa-check");
      // Select and copy the text
      tempInput.select();
      document.execCommand("copy");
      // Remove the temporary input element
      document.body.removeChild(tempInput);
      setTimeout(function() {
        copy.classList.add("fa-copy");
        copy.classList.remove("fa-check");
      }, 2000);
    }
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Get references to relevant elements
      const switchTypeRadio = document.querySelectorAll('input[name="switch-type"]');
      const dynamicSwitchOptions = document.getElementById('dynamic-switch-options');
      // Add event listener to switch type radio buttons
      switchTypeRadio.forEach(function(radio) {
        radio.addEventListener('change', function() {
          if (radio.value === 'dynamic') {
            dynamicSwitchOptions.style.display = 'block';
          } else {
            dynamicSwitchOptions.style.display = 'none';
          }
        });
      });
    });
  </script>

  <?php include 'includes/footer.php' ?>
</body>

</html>