<?php
  session_start();
  // Check if user is logged in
  if (!isset($_SESSION['admin_username'])) {
    header("Location: login");
    exit();
  }
  include 'includes/conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=:id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header("Location: user.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("UPDATE users SET username=:username, email=:email, password=:password WHERE id=:id");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: user.php");
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Edit User</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include 'includes/header.php' ?>
  </head>
  <body>
    <?php include 'includes/nav.php' ?>
    <div class="container">
      <h1>Edit User</h1>
      <form method="POST">
        <label for="username">Username</label>
        <input type="text" name="username" value="<?php echo $user['username']; ?>">
        <label for="email">Email</label>
        <input type="text" name="email" value="<?php echo $user['email']; ?>">
        <input type="submit" value="save"></input>
      </form>
    </div>
  </body>
</html>
