<?php
  session_start();
  require_once 'includes/conn.php';

  // Check if user is logged in
  if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>pCloud | Notifications</title>
<?php include 'includes/header.php' ?>
</head>
<body>
<div class="flex">
  <?php include 'includes/nav.php' ?>
  <main>
  <div class="py-16 px-10">
    <h1 class="text-4xl text-black font-semibold mb-8">Notifications</h1>

  </div>
  </main>
</div>
<?php include 'includes/footer.php' ?>
</body>
</html>
