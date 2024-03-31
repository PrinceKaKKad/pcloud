<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login");
    exit();
}
include 'includes/conn.php';

// Set default values for search and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$perPage = 10; // Number of templates per page

// Retrieve all template details from the database
$stmt = $pdo->prepare("SELECT id, user_id, name, auth, status, time FROM template");
$stmt->execute();
$template = $stmt->fetchAll();

// Filter the templates based on the search keyword
if (!empty($search)) {
    $template = array_filter($template, function ($entry) use ($search) {
        return stripos($entry['name'], $search) !== false;
    });
}

// Calculate the offset for pagination
$totalTemplates = count($template);
$totalPages = ceil($totalTemplates / $perPage);
$offset = ($page - 1) * $perPage;

// Retrieve the templates for the current page
$template = array_slice($template, $offset, $perPage);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Templates</title>
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
        <div class="show-search mb-4 flex justify-center">
            <div class="search-form">
                <form method="GET" action="">
                    <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search by template name" class="px-16 py-2 rounded-l-md focus:outline-none focus:ring focus:border-blue-300">
                    <button type="submit" class="px-16 py-2 bg-blue-500 text-white rounded-r-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Search</button>
                </form>
            </div>
        </div>
        <div class="container mx-auto">
            <div class="user-table bg-white border border-gray-300 shadow-md rounded-md overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase">Username</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase">Template name</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase">Authentication Code</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = $offset + 1;
                        foreach ($template as $template): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $i; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
                                    $stmt->bindParam(':user_id', $template['user_id']);
                                    $stmt->execute();
                                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                                    if ($user) {
                                        echo $user["username"];
                                    } else {
                                        echo "User not found";
                                    }
                                    ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $template['name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $template['auth']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php
                                    $currentTimestamp = time(); // Get the current timestamp
                                    $templateTimestamp = strtotime($template['time']); // Convert the template time to a timestamp

                                    // Check if the status is true and the time difference is less than or equal to 30 seconds
                                    if ($template['status'] === 'true' && ($currentTimestamp - $templateTimestamp) <= 30) {
                                        echo '<p style= "color:green;"> Online </p>';
                                    } else {
                                        echo '<p style= "color:red;"> Offline </p';
                                    }
                                    ?></td>
                            </tr>
                            <?php $i++;
                        endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination flex justify-center mt-8">
                <?php if ($totalPages > 1): ?>
                    <ul class="flex items-center space-x-2">
                        <?php if ($page > 1): ?>
                            <li><a href="?search=<?php echo $search; ?>&page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                            <li<?php if ($pageNumber == $page) echo ' class="active"'; ?>><a href="?search=<?php echo $search; ?>&page=<?php echo $pageNumber; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600"><?php echo $pageNumber; ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li><a href="?search=<?php echo $search; ?>&page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Next</a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function toggleSearchForm() {
            var searchForm = document.querySelector('.search-form');
            searchForm.classList.toggle('hidden');
        }
    </script>
            <?php include 'includes/footer.php' ?>

</body>

</html>
