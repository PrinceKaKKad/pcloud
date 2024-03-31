<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login");
    exit();
}
include 'includes/conn.php';

// Query to retrieve all user details
$stmt = $pdo->prepare("SELECT id, username, email, created_at, status FROM users");
$stmt->execute();
$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php include 'includes/header.php' ?>
</head>

<body>

    <div class="flex h-screen">
        <?php include 'includes/nav.php' ?>
        <main class = "flex-1 overflow-x-hidden overflow-y-auto">
            <div class="py-16 px-10">
                <h1 class="text-4xl text-black font-semibold mb-8">Templates</h1>
                    <div class="container">
            <table class="w-full bg-white shadow-md rounded-md overflow-x-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">#</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Username</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Created Time</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Activation</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $i; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $user['username']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $user['email']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $user['created_at']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php
                                if ($user['status'] === '1') {
                                    echo 'Verified';
                                } else {
                                    echo 'Not Verified';
                                }
                                ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="manage_user.php?id=<?php echo $user['id']; ?>" class="bg-blue-500 text-white py-2 px-4 rounded cursor-pointer">Edit</a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="bg-red-500 text-white py-2 px-4 rounded cursor-pointer" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        </tr>
                        <?php $i++;
                    endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
        <?php include 'includes/footer.php' ?>

</html>
