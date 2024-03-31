<?php
require_once 'includes/conn.php';

// Check if the "verify" parameter exists in the URL
if (isset($_GET['verify'])) {
    // Get the verification string from the URL
    $verificationString = $_GET['verify'];

    // Check if the verification string exists in the database or any other storage method

    // Example check: Assume the verification string is valid
    $verificationValid = false;

    // Check if the verification string exists in the database
    $query = "SELECT COUNT(*) FROM users WHERE verify = :verify";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':verify', $verificationString);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Verification string is valid, allow the user to reset the password
        $verificationValid = true;
    }

    if ($verificationValid) {
        // Check if the form was submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get the new password and confirm password from the form
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            // Validate the new password and confirm password
            if ($newPassword === $confirmPassword) {
                // Passwords match, save the new password

                // Hash the new password
                $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

                // Generate a new verification string
                $newVerificationString = generateRandomString(16); 

                // Update the password and verification string in the database for the user associated with the verification string
                // Assuming you have a users table with columns named "password" and "verify"
                $query = "UPDATE users SET password = :password, verify = :new_verify WHERE verify = :verify";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':new_verify', $newVerificationString);
                $stmt->bindParam(':verify', $verificationString);
                $stmt->execute();

                // Redirect the user to a success page or login page
                  echo "<script>
                        if (confirm('Your Password Has beed changed!')) {
                            window.close();
                        }
                      </script>";
                exit();
            } else {
                // Passwords don't match, display an error message
            echo "
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                const element = document.getElementById('password');
                if (element) {
                  element.style.display = 'block';
                }
              });
            </script>
            ";
            }
        }
 
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password</title>
  <?php include 'includes/header.php' ?>
</head>

<body class="login">
  <div class="container">
    <div class="text-light">
      <h1>Reset Password</h1>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?verify=' . $verificationString); ?>" method="post" class="login-form">
        <div class="form-group">
          <label for="new_password">New Password:</label>
          <input type="password" id="new_password" name="new_password" required><br><br>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password:</label>
          <input type="password" id="confirm_password" name="confirm_password" required><br><br>
        </div>
        <div style="color: red; display: none;" id="password"> Password Dose not match.</div>
        <input type="submit" class="btn btn-primary" value="Reset Password">

      </form>
    </div>
  </div>
</body>

</html>

<?php
    } else {
        // Verification string is invalid, display an error message or redirect to an error page
        echo '<div style="max-width: 600px; margin: 0 auto; padding: 20px; text-align: center;">';
        echo '<h1 style="font-size: 48px;">The Link has been expired.</h1>';
        echo '<p style="font-size: 18px;">Please Naviget<a href="https://pcloud.princekakkad.tech"> Back.</a></p>';
        echo '</div>';
    }
} else {
    // "verify" parameter is missing from the URL, display an error message or redirect to an error page
    echo '<div style="max-width: 600px; margin: 0 auto; padding: 20px; text-align: center;">';
    echo '<h1 style="font-size: 48px;">404 Not Found</h1>';
    echo '<p style="font-size: 18px;">The page you are looking for could not be found.</p>';
    echo '</div>';
}
// Function to generate a random string of specified length
function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>