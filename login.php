<?php
session_start();

// If user is already logged in, redirect to index
if (isset($_SESSION['username'])) {
    header("Location: index");
    exit();
}

require_once('includes/conn.php');

// Check if the remember token exists in the cookie
if (isset($_COOKIE['__pcloud_login_token'])) {
    $token = $_COOKIE['__pcloud_login_token'];
    $query = "SELECT * FROM users WHERE remember_token=:token";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Remember token is valid, log in the user
        $user = $stmt->fetch();
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id']; // Add user id to session
        header("Location: index");
        exit();
    }
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $query = "SELECT * FROM users WHERE username=:username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // User exists
        $user = $stmt->fetch();
        if (password_verify($password, $user['password'])) {
            if ($user['status'] == 0) {
                echo "<script>alert('Please verify your email address before logging in.')</script>";
            } else {
                // Log user login action
                logAction($pdo, $user['id'], $username, 'User Login');

                // Check if "Remember Me" checkbox is selected
                if (isset($_POST['remember'])) {
                    // Generate a unique token for the user
                    $token = bin2hex(random_bytes(16));

                    // Store the token in a cookie that expires in 1 month (2592000 seconds)
                    setcookie('__pcloud_login_token', $token, time() + 2592000, '/');

                    // Store the token in the database for the user
                    $query = "UPDATE users SET remember_token = :token WHERE id = :user_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':token', $token);
                    $stmt->bindParam(':user_id', $user['id']);
                    $stmt->execute();
                }

                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user['id']; // Add user id to session
                header("Location: index");
                exit();
            }
        } else {
            // Log failed login attempt
            logAction($pdo, null, $username, 'Failed Login Attempt');

            echo "<script>alert('Invalid username or password!')</script>";
        }
    } else {
        // Log failed login attempt
        logAction($pdo, null, $username, 'Failed Login Attempt');

        echo "<script>alert('Invalid username or password!')</script>";
    }
}

function logAction($pdo, $userId, $username, $action)
{
    // Insert log entry
    $logTimestamp = date('Y-m-d H:i:s');
    $query = "INSERT INTO log (user_id, username, action, timestamp) VALUES (:user_id, :username, :action, :timestamp)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':timestamp', $logTimestamp);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php include 'includes/header.php' ?>
</head>

<body>
<div class="flex">
    <?php include 'includes/nav.php' ?>
    <main class="flex-grow p-8">
        <div class="container mx-auto flex justify-center items-center h-full">
            <form action="" method="post" class="max-w-md w-full p-8 bg-white rounded shadow-lg">
                <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>

                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" id="username" name="username" placeholder="Username" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="remember" class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="form-checkbox">
                        <span class="ml-2 text-sm">Remember Me!</span>
                    </label>
                </div>

                <div class="mb-4">
                    <button type="submit"
                            class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">
                        Sign in
                    </button>
                </div>

                <div class="text-sm text-center">
                    <a href="register" class="text-blue-500 hover:underline">Register</a>
                    <span class="mx-2">/</span>
                    <a href="send" class="text-blue-500 hover:underline">Forgot Password?</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include 'includes/footer.php' ?>

</body>

</html>
