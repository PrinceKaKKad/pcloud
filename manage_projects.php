<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
  header("Location: login");
  exit();
}
include 'includes/conn.php';

if (isset($_GET['token'])) {
  $auth = $_GET['token'];
  $stmt = $pdo->prepare("SELECT * FROM template WHERE id=:id");
  $stmt->bindValue(':id', $auth, PDO::PARAM_INT);
  $stmt->execute();
  $template = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
  header("Location: index");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];

  $stmt = $pdo->prepare("UPDATE template SET name=:name WHERE id=:id");
  $stmt->bindValue(':name', $name, PDO::PARAM_STR);
  $stmt->bindValue(':id', $auth, PDO::PARAM_INT);
  $stmt->execute();

  // Redirect to index page after updating
  header("Location: index");
  exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
    <?php include 'includes/header.php' ?>
</head>

<body>
<div class="flex">
    <?php include 'includes/nav.php' ?>
    <main class="flex-grow p-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-gray-200 p-3 rounded">
              <li class="breadcrumb-item text-gray-400" aria-current="page"><a href="index">Dashboard</a></li>
              <li class="breadcrumb-item text-gray-600 active"><?php echo $template['name'] ?> Project</li>
            </ol>
          </nav>
        <div class="container flex justify-center items-center h-full">
            <form action="" method="post" class="max-w-md w-full p-8 bg-white rounded shadow-lg">
                <h1 class="text-2xl font-bold mb-6 text-center">Edit Project</h1>

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $template['name']; ?>" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <input type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600" value="Save">
                </div>
            </form>
        </div>
    </main>
</div>
<?php include 'includes/footer.php' ?>

</body>
</html>
