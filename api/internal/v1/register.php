<?php
require_once '../../conn.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get the form data
    $username = $_GET['username'];
    $password = $_GET['password'];
    $email = $_GET['email'];

    // Check if the username already exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $emailRow = $stmt->fetch();

    if ($row) {
        // Username already exists, display an error message
        $response = array('status' => 'error', 'message' => 'Username already exists, please choose a different one.');
        echo json_encode($response);
    } else if ($emailRow) {
        // Email already exists, display an error message
        $response = array('status' => 'error', 'message' => 'Email already in use, please use a different one.');
        echo json_encode($response);
    } else {
        // Generate verification string
        $verificationString = uniqid();

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute the SQL statement to insert the user
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, verify, status) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$username, $hashed_password, $email, $verificationString]);

        // Send verification email
        $verificationLink = "http://pcloud.princekakkad.tech/verification?verify=" . $verificationString;
        $emailSent = sendVerificationEmail($email, $username, $verificationLink);

        if ($emailSent) {
            $response = array('status' => 'success', 'message' => 'Verification email has been sent to your registered email address.');
        } else {
            $response = array('status' => 'error', 'message' => 'Failed to send verification email.');
        }
        echo json_encode($response);
    }
} else {
    // Handle invalid request method
    $response = array('status' => 'error', 'message' => 'Invalid request method.');
    echo json_encode($response);
}

function sendVerificationEmail($recipientEmail, $username, $verificationLink) {
    // Set up the email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: P-Cloud <no-reply@pcloud.princekakkad.tech>\r\n";

    // Set up the email subject and body
    $subject = "Account Verification";
    $message = "
    <html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h3 {
            font-size: 24px;
            color: #007bff;
        }
        .otp {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }
        .instructions {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 800px;
            margin-bottom: 20px;
        }
        .webpage-link {
            color: #007bff;
            text-decoration: none;
        }
        .danger {
            color: red;
            text-decoration: none;
        }

    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img class='logo' src='https://pcloud.princekakkad.tech/assets/images/email-logo.png' alt='P Cloud'>
            <h3>Email Verification</h3>
        </div>
        <div class='instructions'>
        <p class='message'>Hello $username. This Email is regarding to your verification with pcloud. your registerd email $recipientEmail.</p>
        <p class='message'>Thank you for registering an account with us. Please click the link below to verify your email address:</p>
        <p class='otp'><a href='$verificationLink'>Verify Email.</a></p>
            <p class='danger'>Please don't share Verification with anyone else.</p>
            <p>Visit our <a class='webpage-link' href='https://pcloud.princekakkad.tech/'>Website</a> for more information.</p>
        </div>
    </div>
</body>
</html>
    ";

    // Send the email
    return mail($recipientEmail, $subject, $message, $headers);
}
   header('Content-Type: application/json');
?>
