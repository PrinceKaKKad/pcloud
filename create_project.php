<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
  header("Location: login");
  exit();
}

include 'includes/conn.php';

// Include the logging code here
$logQuery = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
$logStmt = $pdo->prepare($logQuery);

if (isset($_POST['submit'])) {
  $name = $_POST['name'];
  $user_id = $_SESSION['user_id'];
  $auth = generateRandomString(30);
  $status = 'false';

  $stmt = $pdo->prepare("INSERT INTO template (name, user_id, auth, status) VALUES (:name, :user_id, :auth, :status)");
  $stmt->bindParam(':name', $name);
  $stmt->bindParam(':user_id', $user_id);
  $stmt->bindParam(':auth', $auth);
  $stmt->bindParam(':status', $status);

  // Execute the query
  if ($stmt->execute()) {
    // Log the action
    $logStmt->bindValue(':user_id', $user_id);
    $logStmt->bindValue(':username', $_SESSION['username']);
    $logStmt->bindValue(':action', 'Created template: ' . $name . ' with auth code: ' . $auth);
    $logStmt->bindValue(':timestamp', date('Y-m-d H:i:s'));
    $logStmt->execute();

    header("Location: index");
  } else {
    echo "Something went wrong. Please try again later.";
  }
}

function generateRandomString($length = 30) {
  $prefix = 'pcloud-';
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';

  // Generate (length - prefix length) characters
  for ($i = 0; $i < $length - strlen($prefix); $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }

  return $prefix . $randomString;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project</title>
    <?php include 'includes/header.php' ?>
</head>

<body>
<div class="flex">
    <?php include 'includes/nav.php' ?>
    <main class="flex-grow p-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-gray-200 p-3 rounded">
              <li class="breadcrumb-item text-gray-400" aria-current="page"><a href="index">Dashboard</a></li>
              <li class="breadcrumb-item text-light active">Create Project</li>
            </ol>
          </nav>
        <div class="container flex justify-center items-center h-full">
            <form action="" method="post" class="max-w-md w-full p-8 bg-white rounded shadow-lg">
                <h1 class="text-2xl font-bold mb-6 text-center">Create Project</h1>

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <input type="submit" name="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600" value="Save">
                </div>
            </form>
        </div>
    </main>
</div>
<?php include 'includes/footer.php' ?>

</body>
</html>
