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


// Fetch ticket details
if(isset($_GET['ticket_number']) && !empty(trim($_GET['ticket_number']))){
    $ticket_number = trim($_GET['ticket_number']);
    
    // Fetch ticket details
    $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE ticket_number = ?");
    $stmt->execute([$ticket_number]);
    $ticket = $stmt->fetch();

    // Check if ticket exists
    if(!$ticket){
        // Redirect to admin supports page if ticket not found
        header("Location: support");
        exit();
    }

    // Fetch ticket messages
    $stmt = $pdo->prepare("SELECT * FROM support_messages WHERE ticket_number = ?");
    $stmt->execute([$ticket_number]);
    $messages = $stmt->fetchAll();
} else {
    // Redirect to admin supports page if ticket_number is not provided
    header("Location: support");
    exit();
}
// Handle message submission
// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'];
    
    // Fetch user_id based on ticket_number
    $stmt = $pdo->prepare("SELECT user_id FROM support_tickets WHERE ticket_number = ?");
    $stmt->execute([$ticket_number]);
    $result = $stmt->fetch();

    if ($result) {
        $user_id = $result['user_id'];

        // Insert the new message into the database
        $stmt = $pdo->prepare("INSERT INTO support_messages (ticket_number, user_id, admin_id, message, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$ticket_number, $user_id, $admin_id, $message]);

        // Redirect to admin_view_ticket page
        header("Location: view_ticket?ticket_number=$ticket_number");
        exit();
    } else {
        // Handle the case where the ticket_number is not valid
        // You can redirect or show an error message
        echo "Invalid ticket number";
        exit();
    }
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
            max-height: 60vh;
            /* Limit the height of the container */
            overflow-y: auto;
            /* Enable vertical scrolling */
            scroll-behavior: smooth;
            /* Smooth scrolling */
        }
    </style>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

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
                            <div class="flex <?php echo ($message['admin_id'] == $admin_id) ? 'justify-end' : 'justify-start'; ?> items-center space-x-2"
                                data-message-id="<?php echo $message['id']; ?>">
                                <div class="bg-white rounded-lg shadow-md py-2 px-4 max-w-md">
                                    <p class="font-semibold"><?php echo ($message['admin_id'] == $admin_id) ? 'You' : $ticket['username']; ?>:</p>
                                    <p class="text-gray-700"><?php echo $message['message']; ?></p>
                                    <p class="text-xs text-gray-500" data-timestamp="<?php echo $message['timestamp']; ?>"></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="flex justify-between">
                        <div class="flex-grow">
                            <form id="message-form"
                                action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?ticket_number=<?php echo $ticket['ticket_number']; ?>"
                                method="post" class="flex justify-between p-4 bg-gray-100 rounded-lg">
                                <div id="editor" class="flex-grow"></div>
                                <button type="submit" id="send-button"
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg focus:outline-none"
                                    disabled>Send</button>
                            </form>

                        </div>
                        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                        <script>
                            // Initialize Quill editor with custom toolbar options
                            var quill = new Quill('#editor', {
                                theme: 'snow', // Use the 'snow' theme for a clean editor interface
                                modules: {
                                    toolbar: [
                                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                                        ['bold', 'italic', 'underline', 'strike'], // Text formatting options
                                        ['link', 'image'], // Insert links and images
                                        [{ 'list': 'ordered' }, { 'list': 'bullet' }], // List options
                                        [{ 'align': [] }], // Text alignment options
                                        ['clean'] // Remove formatting button
                                    ]
                                }
                            });

                            // Enable or disable send button based on Quill content
                            quill.on('text-change', function (delta, oldDelta, source) {
                                var sendButton = document.getElementById('send-button');
                                if (quill.getText().trim().length > 0) {
                                    sendButton.removeAttribute('disabled');
                                } else {
                                    sendButton.setAttribute('disabled', 'disabled');
                                }
                            });

                            // Submit form with Quill content on button click
                            document.getElementById('message-form').addEventListener('submit', function (event) {
                                event.preventDefault();
                                var messageInput = document.createElement('input');
                                messageInput.type = 'hidden';
                                messageInput.name = 'message';
                                messageInput.value = quill.root.innerHTML; // Get Quill's HTML content
                                this.appendChild(messageInput);
                                this.submit();
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
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const newMessages = JSON.parse(xhr.responseText);
                    newMessages.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `flex ${message.admin_id == <?php echo $admin_id; ?> ? 'justify-end' : 'justify-start'} items-center space-x-2`;
                        messageDiv.setAttribute('data-message-id', message.id);
                        messageDiv.innerHTML = `
                            <div class="bg-white rounded-lg shadow-md py-2 px-4 max-w-md">
                                <p class="font-semibold">${message.admin_id == <?php echo $admin_id; ?> ? 'You' : '<?php echo $ticket['username']; ?>'}:</p>
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
