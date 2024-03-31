<?php
session_start();

// If user is already logged in, redirect to dashboard
if(isset($_SESSION['admin_username'])) {
  header("Location:index");
  exit();
}

require_once('includes/conn.php');

if(isset($_POST['username']) && isset($_POST['password'])){
  $username = $_POST['username'];
  $password = $_POST['password'];
  $query = "SELECT * FROM admins WHERE username=:username";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':username', $username);
  $stmt->execute();

  if($stmt->rowCount() > 0){
    // User exists
    $user = $stmt->fetch();
    if(password_verify($password, $user['password'])) {
      $_SESSION['admin_username'] = $username;
      header("Location: index.php");
      exit();
    } else {
      echo "<script>alert('Invalid username or password!')</script>";
    }
  } else{
    // User doesn't exist
    echo "<script>alert('Invalid username or password!')</script>";
  }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <?php include 'includes/header.php' ?>
</head>

<body class="h-screen flex justify-center items-center">
    <?php include 'includes/nav.php' ?>
    <main class="flex-grow p-8">
        <div class="container mx-auto flex justify-center items-center h-full">
        <form action="" method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 w-full max-w-md">
            <h1 class="text-2xl text-center font-bold mb-4">Admin Login</h1>
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                <input type="text" id="username" name="username" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
                <input type="submit" value="Login"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            </div>
        </form>
    </div>
</body>
    <?php include 'includes/footer.php' ?>
</html>
