<?php
session_start();
require_once 'includes/conn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login");
    exit();
}

// Fetch admin id based on admin_username
$admin_username = $_SESSION['admin_username'];
$stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
$stmt->execute([$admin_username]);
$admin = $stmt->fetch();
$admin_id = $admin['id'];

// Fetch all tickets
$stmt = $pdo->prepare("SELECT st.*, u.username FROM support_tickets st JOIN users u ON st.user_id = u.id");
$stmt->execute();
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>pCloud | Admin Supports</title>
    <?php include 'includes/header.php' ?>
</head>
<body>
<body>
<div class="flex">
    <?php include 'includes/nav.php' ?>
    <main class="flex-1">
        <div class="py-16 px-10">
            <h1 class="text-4xl text-black font-semibold mb-8">Admin Supports</h1>
            <div>
                <h2 class="text-2xl mb-4">All Tickets</h2>
                <?php foreach ($tickets as $ticket) { ?>
                    <div class="bg-gray-300 rounded-lg p-4 mb-4">
                        <a href="view_ticket?ticket_number=<?php echo $ticket['ticket_number']; ?>" class="text-blue-600 hover:underline">
                            <h3 class="text-lg font-semibold"><?php echo $ticket['topic']; ?></h3>
                            <p class="text-sm text-gray-600">User: <?php echo $ticket['username']; ?></p>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>
</div>
<?php include 'includes/footer.php' ?>
</body>
</html>
