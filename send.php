<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include the PHPMailer autoloader
require "./phpmailer/PHPMailer.php";
require "./phpmailer/SMTP.php";
require "./phpmailer/Exception.php";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email address from the form
    $email = $_POST['email'];

    // Generate a random verification string
    $verificationString = uniqid();

    // Save the verification string and email to the database or any other storage method
    // Assuming you have a database connection
    require_once('includes/conn.php');
    $query = "UPDATE users SET verify=:verify WHERE email=:email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':verify', $verificationString);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Check if the update was successful
    if ($stmt->rowCount() > 0) {
        // Send the verification link to the user's email
        $verificationLink = "https://pcloud.codeestro.com/forget?verify=" . $verificationString;

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = "smtp.hostinger.com";
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Username = "info@codeestro.com"; // Update with your SMTP username
            $mail->Password = "Cfjx@136"; // Update with your SMTP password

            // Sender and recipient
            $mail->setFrom("info@codeestro.com", "pCloud"); // Update with the sender email and name
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";

            // Construct the email body
            $mail->Body = "
                <html>
                <head>
                    <style>
                        /* Your email styles here */
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <img class='logo' src='https://pcloud.codeestro.com/assets/images/email-logo.png' alt='P Cloud'>
                            <h3>Forget Password</h3>
                        </div>
                        <div class='instructions'>
                            <p class='message'>Please click the link below to change your password:</p>
                            <p class='otp'><a href='{$verificationLink}'>Reset Password.</a></p>
                            <p class='danger'>Please don't share link with anyone else.</p>
                            <p>Visit our <a class='webpage-link' href='https://pcloud.codeestro.com/'>Website</a> for more information.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mail->send();

            echo "<script>
                if (confirm('Forget link has been sent to your email address.')) {
                    window.location.href = 'login';
                }
            </script>";
        } catch (Exception $e) {
            echo "<script>
                if (confirm('There is a problem in sending the email. Please try again later.')) {
                }
            </script>";
            // Uncomment the next line for debugging purposes:
            // echo "Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>
            alert('Email Address is not registered!');
        </script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
    <?php include 'includes/header.php' ?>
</head>
<body>
    <div class="flex">
        <?php include 'includes/nav.php' ?>
        <main class="flex-grow">
            <div class="container mx-auto flex justify-center items-center h-full">
                <form action="" method="post" class="max-w-md w-full p-8 bg-white rounded shadow-lg">
                    <h1 class="text-2xl font-bold mb-6 text-center">Forget Password</h1>

                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                        <input type="email" id="email" name="email"
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:border-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <input type="submit"
                            class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600"
                            value="Send Verification Link">
                    </div>

                    <div class="create-account-link text-center">
                        <a href="register" class="text-blue-500 hover:underline">Register</a>
                        <span class="mx-2">/</span>
                        <a href="login" class="text-blue-500 hover:underline">Login</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <?php include 'includes/footer.php' ?>
</body>
</html>
