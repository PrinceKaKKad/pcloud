<?php
session_start();
require_once 'includes/conn.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit();
}

// Fetch user_id and ticket_number
$user_id = $_SESSION['user_id'];
$ticket_number = isset($_GET['ticket_number']) ? trim($_GET['ticket_number']) : '';

// Update the seen column for messages associated with the user_id and ticket_number
$stmt = $pdo->prepare("UPDATE support_messages SET seen = 1 WHERE user_id = ? AND ticket_number = ?");
$stmt->execute([$user_id, $ticket_number]);

// Fetch ticket details
if(isset($_GET['ticket_number']) && !empty(trim($_GET['ticket_number']))){
    $ticket_number = trim($_GET['ticket_number']);
    
    // Fetch ticket details
    $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE ticket_number = ?");
    $stmt->execute([$ticket_number]);
    $ticket = $stmt->fetch();

    // Check if ticket exists
    if(!$ticket){
        // Redirect to supports page if ticket not found
        header("Location: support");
        exit();
    }

    // Fetch ticket messages
    $stmt = $pdo->prepare("SELECT * FROM support_messages WHERE ticket_number = ?");
    $stmt->execute([$ticket_number]);
    $messages = $stmt->fetchAll();
} else {
    // Redirect to supports page if ticket_number is not provided
    header("Location: support");
    exit();
}

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];
    
    // Insert the new message into the database
    $stmt = $pdo->prepare("INSERT INTO support_messages (ticket_number, user_id, message, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$ticket_number, $user_id, $message]);

    // Redirect to view_ticket page
    header("Location: view_ticket?ticket_number=$ticket_number");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>pCloud | View Ticket</title>
    <?php include 'includes/header.php' ?>
    <style>
        .message-container {
            max-height: 60vh; /* Limit the height of the container */
            overflow-y: auto; /* Enable vertical scrolling */
            scroll-behavior: smooth; /* Smooth scrolling */
        }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <?php include 'includes/nav.php' ?>
        <main class="flex-1 overflow-x-hidden overflow-y-auto">
            <div class="py-16 px-10">
                <h1 class="text-4xl text-black font-semibold mb-8">View Ticket</h1>
                <div class="flex flex-col space-y-8">
                    <div class="flex justify-center items-center bg-gray-300 py-2 px-4 rounded-lg">
                        <h2 class="text-lg font-semibold"><?php echo $ticket['topic']; ?></h2>
                        <span class="ml-2 text-gray-600">#<?php echo $ticket['ticket_number']; ?></span>
                    </div>
                    <div class="space-y-4 message-container" id="message-container">
                        <?php foreach ($messages as $message) { ?>
                            <div class="flex <?php echo ($message['admin_id'] == !$_SESSION['user_id']) ? 'justify-end' : 'justify-start'; ?> items-center space-x-2" data-message-id="<?php echo $message['id']; ?>">
                                <div class="bg-white rounded-lg shadow-md py-2 px-4 max-w-md">
                                    <p class="font-semibold"><?php echo ($message['admin_id'] == !$_SESSION['user_id']) ? 'You' : 'Admin'; ?>:</p>
                                    <p class="text-gray-700"><?php echo $message['message']; ?></p>
                                    <p class="text-xs text-gray-500" data-timestamp="<?php echo $message['timestamp']; ?>"></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="flex justify-between">
                        <div class="flex-grow">
                            <form id="message-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?ticket_number=<?php echo $ticket['ticket_number']; ?>" method="post" class="flex justify-between p-4 bg-gray-100 rounded-lg">
                                <input type="text" name="message" id="message-input" class="flex-grow mr-2 bg-white border border-gray-300 rounded-lg p-2 focus:outline-none" placeholder="Type your message here...">
                                <button type="submit" id="send-button" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg focus:outline-none" disabled>Send</button>
                            </form>
                        </div>

                        <script>
                            document.getElementById('message-input').addEventListener('input', function() {
                                const message = this.value.trim();
                                const sendButton = document.getElementById('send-button');
                                if (message.length > 0) {
                                    sendButton.removeAttribute('disabled');
                                } else {
                                    sendButton.setAttribute('disabled', 'disabled');
                                }
                            });
                        </script>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include 'includes/footer.php' ?>

    <script>
        // Scroll to the bottom of the message container
        document.getElementById('message-container').scrollTop = document.getElementById('message-container').scrollHeight;

        // AJAX function to fetch new messages
        function fetchNewMessages() {
            const lastMessageId = document.querySelector('.message-container > div:last-child').getAttribute('data-message-id');
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `includes/fetch_messages.php?ticket_number=<?php echo $ticket_number; ?>&last_message_id=${lastMessageId}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const newMessages = JSON.parse(xhr.responseText);
                    newMessages.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `flex ${message.admin_id != <?php echo $_SESSION['user_id']; ?> ? 'justify-end' : 'justify-start'} items-center space-x-2`;
                        messageDiv.setAttribute('data-message-id', message.id);
                        messageDiv.innerHTML = `
                            <div class="bg-white rounded-lg shadow-md py-2 px-4 max-w-md">
                                <p class="font-semibold">${message.admin_id != <?php echo $_SESSION['user_id']; ?> ? 'You' : 'Admin'}:</p>
                                <p class="text-gray-700">${message.message}</p>
                                <p class="text-xs text-gray-500" data-timestamp="${message.timestamp}"></p>
                            </div>
                        `;
                        document.querySelector('.message-container').appendChild(messageDiv);
                        
                        // Update timestamp to "X hours ago" or "X minutes ago"
                        const timestamp = messageDiv.querySelector('[data-timestamp]');
                        const time = new Date(timestamp.getAttribute('data-timestamp'));
                        const now = new Date();
                        const diffMs = now - time;
                        const diffMins = Math.round(diffMs / 60000);
                        const diffHours = Math.round(diffMins / 60);
                        if (diffHours > 0) {
                            timestamp.textContent = `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
                        } else {
                            timestamp.textContent = `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
                        }
                    });
                    // Scroll to the bottom of the message container
                    document.getElementById('message-container').scrollTop = document.getElementById('message-container').scrollHeight;
                }
            };
            xhr.send();
        }


        // Fetch new messages every 1 second
        setInterval(fetchNewMessages, 1000);

        // Submit message form using AJAX
        document.getElementById('message-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', this.getAttribute('action'), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    fetchNewMessages(); // Fetch new messages after sending
                    document.getElementById('message-form').reset(); // Clear the form after sending
                }
            };
            xhr.send(formData);
        });

        // Update timestamp to "X hours ago" or "X minutes ago"
        const timestamps = document.querySelectorAll('[data-timestamp]');
        timestamps.forEach(timestamp => {
            const time = new Date(timestamp.getAttribute('data-timestamp'));
            const now = new Date();
            const diffMs = now - time;
            const diffMins = Math.round(diffMs / 60000);
            const diffHours = Math.round(diffMins / 60);
            if (diffHours > 0) {
                timestamp.textContent = `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            } else {
                timestamp.textContent = `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            }
        });
    </script>
</body>
</html>
