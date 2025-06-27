<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the updated PHPMailer autoloader
require "./phpmailer/PHPMailer.php";
require "./phpmailer/SMTP.php";
require "./phpmailer/Exception.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize a variable to track success
$messageSent = false;

// Your email credentials
$smtp_host = "smtp.hostinger.com";
$smtp_port = 587;
$smtp_username = "email";
$smtp_password = "password";

// Recipient email address
$recipient_email = "princekakkad10@gmail.com";

// User input from the form
$user_name = $_POST["full-name"];
$user_email = $_POST["email"];
$subject = $_POST["subject"];
$message = $_POST["message"];

try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->Port = $smtp_port;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;

    // Sender and recipient
    $mail->setFrom($smtp_username, $user_name);
    $mail->addAddress($recipient_email);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = $subject;

    // Construct the email body with sender details
    $emailBody = "
        <html>
        <head>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                h2 {
                    color: #333;
                }
                p {
                    margin-bottom: 15px;
                }
                strong {
                    color: #007bff;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> {$user_name}</p>
                <p><strong>Email:</strong> {$user_email}</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Message:</strong><br>{$message}</p>
            </div>
        </body>
        </html>
    ";

    $mail->Body = $emailBody;

    if (!$mail->send()) {
        throw new Exception("Mailer Error: " . $mail->ErrorInfo);
    }

    // Set the variable to indicate success
    $messageSent = true;
} catch (Exception $e) {
    $messageSent = false;
    $errorMessage = $e->getMessage();
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode(['success' => $messageSent, 'message' => $errorMessage ?? '']);
?>
