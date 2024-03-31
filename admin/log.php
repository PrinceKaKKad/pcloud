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
$perPage = 10; // Number of log entries per page

// Retrieve all log entries from the database
$stmt = $pdo->prepare("SELECT * FROM log ORDER BY timestamp DESC");
$stmt->execute();
$logEntries = $stmt->fetchAll();

// Filter the log entries based on the search keyword and action keyword
if (!empty($search)) {
    $logEntries = array_filter($logEntries, function ($entry) use ($search) {
        return stripos($entry['username'], $search) !== false || stripos($entry['action'], $search) !== false;
    });
}

// Calculate the offset for pagination
$totalEntries = count($logEntries);
$totalPages = ceil($totalEntries / $perPage);
$offset = ($page - 1) * $perPage;

// Retrieve the log entries for the current page
$logEntries = array_slice($logEntries, $offset, $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Log</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'includes/header.php' ?>
</head>
<body>

    <div class="flex h-screen">
        <?php include 'includes/nav.php' ?>
        <main class = "flex-1 overflow-x-hidden overflow-y-auto">
            <div class="py-16 px-10">
                <h1 class="text-4xl text-black font-semibold mb-8">User Log</h1>
                    <div class="container">

<div class="flex flex-col min-h-screen">
    <main class="flex-grow">
        <div class="container mx-auto py-8">
            <!-- <h1 class="text-4xl font-semibold text-center mb-8">User Log</h1> -->
            <div class="show-search mb-6 flex justify-center">
                <div class="search-form">
                    <form method="GET" action="">
                        <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search by username or action" class="px-16 py-2 rounded-l-md focus:outline-none focus:ring focus:border-blue-300">
                        <button type="submit" class="px-16 py-2 bg-blue-500 text-white rounded-r-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Search</button>
                    </form>
                </div>
            </div>
            <div class="user-table mb-8">
                <table class="w-full bg-white border border-gray-200 shadow-md rounded-md">
                    <thead class="bg-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">Timestamp</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    <?php $i = ($page - 1) * $perPage + 1; foreach ($logEntries as $logEntry): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $i; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $logEntry['username']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $logEntry['action']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $logEntry['timestamp']; ?></td>
                        </tr>
                        <?php $i++; endforeach; ?>
                    </tbody>
                </table>
            </div>
                <div class="pagination flex justify-center">
                    <?php if ($totalPages > 1): ?>
                        <ul class="flex items-center space-x-2">
                            <?php if ($page > 1): ?>
                                <li><a href="?search=<?php echo $search; ?>&page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Previous</a></li>
                            <?php endif; ?>
                
                            <?php
                            // Calculate start and end page numbers to display
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                
                            // Display ellipsis and first page if needed
                            if ($startPage > 1) {
                                echo '<li><a href="?search=' . $search . '&page=1" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="px-4 py-2">...</li>';
                                }
                            }
                
                            // Display page numbers with highlighting for the current page
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $isActive = $page == $i ? 'bg-blue-100 text-black' : 'bg-blue-500 text-white';
                                echo '<li><a href="?search=' . $search . '&page=' . $i . '" class="px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600 ' . $isActive . '">' . $i . '</a></li>';
                            }
                
                            // Display ellipsis and last page if needed
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="px-4 py-2">...</li>';
                                }
                                echo '<li><a href="?search=' . $search . '&page=' . $totalPages . '" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">' . $totalPages . '</a></li>';
                            }
                            ?>
                
                            <?php if ($page < $totalPages): ?>
                                <li><a href="?search=<?php echo $search; ?>&page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                </div>
        </div>
    </main>
    <?php include 'includes/footer.php' ?>
</div>
<script>
    function toggleSearchForm() {
        var searchForm = document.querySelector('.search-form');
        searchForm.classList.toggle('hidden');
    }
</script>
</body>
</html>
