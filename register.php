<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include the PHPMailer autoloader
require "./phpmailer/PHPMailer.php";
require "./phpmailer/SMTP.php";
require "./phpmailer/Exception.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include the database connection
    require_once 'includes/conn.php';

    $logQuery = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
    $logStmt = $pdo->prepare($logQuery);

    // Get the form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Check if the username already exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    // Check if the email already exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $emailRow = $stmt->fetch();

    if ($row) {
        // Username already exists, display an error message
        echo "<script>alert('Username already exists, please choose a different one.')</script>";
    } else if ($emailRow) {
        // Email already exists, display an error message
        echo "<script>alert('Email already in use, please use a different one.')</script>";
    } else {
        // Generate verification string
        $verificationString = uniqid();

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute the SQL statement to insert the user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, verify, status) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$username, $hashed_password, $email, $verificationString]);

        $logStmt->bindValue(':user_id', 'NEW_USER');
        $logStmt->bindValue(':username', $username);
        $logStmt->bindValue(':action', 'Just created an account with username: ' . $username);
        $logStmt->bindValue(':timestamp', date('Y-m-d H:i:s'));
        $logStmt->execute();

        // Send verification email
        $verificationLink = "http://pcloud.codeestro.com/verification?verify=" . $verificationString;
        sendVerificationEmail($email, $username, $verificationLink);

        echo "<script>
            if (confirm('Verification Email has been sent to your registered email address.')) {
                window.location.href = 'login';
            }
        </script>";
        exit();
    }
}

function sendVerificationEmail($recipientEmail, $username, $verificationLink)
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = "smtp.hostinger.com";
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Username = "info@codeestro.com"; // Update with your SMTP username
    $mail->Password = "Cfjx@136"; // Update with your SMTP password

    // Recipient and sender
    $mail->setFrom("info@codeestro.com", "pCloud");
    $mail->addAddress($recipientEmail, $username);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "Account Verification";
    $mail->Body = "
        <html>
        <head>
            <style>
                /* Your email styles go here */
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img class='logo' src='https://pcloud.codeestro.com/assets/images/email-logo.png' alt='P Cloud'>
                    <h3>Email Verification</h3>
                </div>
                <div class='instructions'>
                    <p class='message'>Hello $username. This Email is regarding your verification with pcloud. Your registered email $recipientEmail.</p>
                    <p class='message'>Thank you for registering an account with us. Please click the link below to verify your email address:</p>
                    <p class='otp'><a href='$verificationLink'>Verify Email.</a></p>
                    <p class='danger'>Please don't share Verification with anyone else.</p>
                    <p>Visit our <a class='webpage-link' href='https://pcloud.codeestro.com/'>Website</a> for more information.</p>
                </div>
            </div>
        </body>
        </html>
    ";

    try {
        $mail->send();
        echo "Verification email sent successfully.";
    } catch (Exception $e) {
        echo "Failed to send verification email. Error: {$mail->ErrorInfo}";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <?php include 'includes/header.php' ?>
</head>

<body>
<div class="flex">
    <?php include 'includes/nav.php' ?>
    <main class="flex-grow">

    <div class="container mx-auto flex justify-center items-center h-full">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
            class="max-w-md w-full p-8 bg-white rounded shadow-lg">
            <h1 class="text-2xl font-bold mb-6 text-center">User Registration</h1>

            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                <input type="text" id="username" name="username" pattern="[a-z]+" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                <small class="text-gray-500">Username must be in small characters and No spaces allowed</small>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                <small class="text-gray-500">Password must be 8 characters long and include at least one number, one
                    uppercase letter, one lowercase letter, and one special character.</small>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:border-blue-500">
            </div>

            <div class="mb-4">
                <input type="submit"
                    class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600"
                    value="Register">
            </div>

            <div class="text-sm text-center">
                <a href="login" class="text-blue-500 hover:underline">Already have an account?</a>
            </div>
        </form>
    </div>
</main>
</div>
    <?php include 'includes/footer.php' ?>

    <script>
        function validatePassword() {
            var password = document.getElementById("password").value;
            var pattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
            if (!pattern.test(password)) {
                alert("Password must be 8 characters long and include at least one number, one uppercase letter, one lowercase letter, and one special character.");
                return false;
            }
            return true;
        }

        function validateUsername() {
            var username = document.getElementById("username").value;
            if (username.includes(" ")) {
                alert("Username cannot contain spaces.");
                return false;
            }
            return true;
        }

        document.querySelector("form").addEventListener("submit", function (e) {
            if (!validatePassword() || !validateUsername()) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>