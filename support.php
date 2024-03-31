<?php
session_start();
require_once 'includes/conn.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit();
}

// Fetch all user's tickets
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ?");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $topic = $_POST['topic'];

    // Generate random ticket number
    $ticket_number = uniqid();
    $message = 'Your ticket ID: ' . $ticket_number . '<br> Topic Name: ' . $topic . '<br> User Name: ' . $username;


    // Insert the new support ticket into the database
    $stmt = $pdo->prepare("INSERT INTO support_tickets (ticket_number, user_id, username, topic) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ticket_number, $user_id, $username, $topic]);

    $stmt = $pdo->prepare("INSERT INTO support_messages (ticket_number, user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$ticket_number, $user_id, $message]);

    // Redirect to the supports page
    header("Location: support");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>pCloud | Supports</title>
    <?php include 'includes/header.php' ?>
</head>

<body>
    <div class="flex">
        <?php include 'includes/nav.php' ?>
        <main class="flex-1">
            <div class="py-16 px-10">
                <h1 class="text-4xl font-semibold mb-8">Supports</h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php if (!empty($tickets)) { ?>
                        <?php foreach ($tickets as $ticket) { ?>
                            <div class="col">
                                <div class="bg-white dark:bg-gray-700 h-full rounded-lg shadow-md">
                                    <div class="p-6">
                                        <a href="view_ticket?ticket_number=<?php echo $ticket['ticket_number']; ?>" class="text-decoration-none">
                                            <h5 class="text-xl font-semibold mb-2"><?php echo $ticket['topic']; ?></h5>
                                        </a>
                                        <div class="text-gray-600">
                                            <b>Ticket Number:</b> <?php echo $ticket['ticket_number']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="text-gray-500 col-span-full text-center">No tickets found.</p>
                    <?php } ?>
                </div>
                <div class="mt-8">
                    <h2 class="text-2xl mb-4">Create New Ticket</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="max-w-md mx-auto">
                        <div class="mb-4">
                            <label for="topic" class="block text-gray-700 dark:text-gray-300">Topic:</label>
                            <input type="text" id="topic" name="topic" class="mt-1 p-2 border rounded-md w-full focus:outline-none focus:ring focus:border-blue-500 dark:bg-gray-600 dark:text-gray-100">
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600">Submit</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <?php include 'includes/footer.php' ?>
</body>

</html>
