<?php
  session_start();
  // Check if user is logged in
  if (!isset($_SESSION['admin_username'])) {
    header("Location: login");
    exit();
  }
  include 'includes/conn.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php include 'includes/header.php' ?>
</head>

<body>
    <div class="flex h-screen">
        <?php include 'includes/nav.php' ?>
        <main class = "flex-1 overflow-x-hidden overflow-y-auto">
            <div class="py-16 px-10">
                <h1 class="text-4xl text-black font-semibold mb-8">Dashboard</h1>
                    <div class="container">
                        <?php
                        $query = "SELECT COUNT(*) as total FROM users";
                        $stmt = $pdo->query($query);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $total_users = $row['total'];
                        ?>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="text-xl font-bold">Users</div>
                            <div class="mt-4 text-4xl font-bold text-gray-900"><?php echo $total_users ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
        <?php include 'includes/footer.php' ?>
</html>
