<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login");
    exit();
}

$sitemapFile = '../sitemap.xml';

// Check if form is submitted
if (isset($_POST['submit'])) {
    $pageUrl = 'https://' . $_POST['page_url'];
    $lastModified = $_POST['last_modified'];
    $priority = $_POST['priority'];

    // Append the new page URL to the existing sitemap XML file
    $sitemapContent = file_get_contents($sitemapFile);
    $updatedSitemapContent = str_replace('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', '<urlset>', $sitemapContent);
    $newUrlElement = '<url>' . PHP_EOL;
    $newUrlElement .= '<loc>' . htmlspecialchars($pageUrl) . '</loc>' . PHP_EOL;
    $newUrlElement .= '<lastmod>' . $lastModified . '</lastmod>' . PHP_EOL;
    $newUrlElement .= '<priority>' . $priority . '</priority>' . PHP_EOL;
    $newUrlElement .= '</url>' . PHP_EOL;
    $updatedSitemapContent = str_replace('</urlset>', $newUrlElement . '</urlset>', $updatedSitemapContent);
    file_put_contents($sitemapFile, $updatedSitemapContent);

    echo '<script>alert("Page URL added to sitemap successfully.");</script>';
}

// Check if delete action is requested
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];

    // Retrieve the existing sitemap details
    $sitemapContent = file_get_contents($sitemapFile);
    $xml = simplexml_load_string($sitemapContent);

    // Check if the index is valid
    if (isset($xml->url[$index])) {
        // Remove the specified URL from the sitemap
        unset($xml->url[$index]);

        // Save the updated sitemap content
        file_put_contents($sitemapFile, $xml->asXML());

        echo '<script>alert("Page URL deleted from sitemap successfully.");</script>';
    }
}

// Check if edit action is requested
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];

    // Retrieve the existing sitemap details
    $sitemapContent = file_get_contents($sitemapFile);
    $xml = simplexml_load_string($sitemapContent);

    // Check if the index is valid
    if (isset($xml->url[$index])) {
        $url = $xml->url[$index]->loc;
        $lastModified = $xml->url[$index]->lastmod;
        $priority = $xml->url[$index]->priority;
    }
}

// Retrieve and display the existing sitemap details
$sitemapContent = file_get_contents($sitemapFile);
$xml = simplexml_load_string($sitemapContent);
$urls = $xml->url;

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Sitemap</title>
    <?php include 'includes/header.php' ?>
</head>

<body>
    <div class="flex h-screen">
        <?php include 'includes/nav.php' ?>
        <main class = "flex-1 overflow-x-hidden overflow-y-auto">
            <div class="py-16 px-10">
                <h1 class="text-4xl text-black font-semibold mb-8">Manage Sitemap</h1>
                    <div class="container">
            <div class="user-table">
                <p>Add Link: <a id="add-switch-button" class="edit bg-green-500 text-white py-2 px-4 rounded cursor-pointer">NEW</a></p>
            </div>
            <div class="container mx-auto">
                <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['index'])) {
                    if (isset($url)) {
                ?>
                        <form class="mt-4" method="POST">
                            <h2 class="text-xl font-semibold">Edit Page in Sitemap</h2>
                            <input type="hidden" name="index" value="<?php echo $index; ?>">
                            <label class="block mt-4">Page URL:</label>
                            <input type="text" name="page_url" value="<?php echo $url; ?>" class="w-full rounded border-gray-300 py-2 px-4" required>
                            <label class="block mt-4">Last Modified:</label>
                            <input type="text" name="last_modified" value="<?php echo $lastModified; ?>" class="w-full rounded border-gray-300 py-2 px-4" required>
                            <label class="block mt-4">Priority:</label>
                            <input type="number" name="priority" min="0" max="1" step="0.1" value="<?php echo $priority; ?>" class="w-full rounded border-gray-300 py-2 px-4" required>
                            <input type="submit" name="submit" value="Update Sitemap" class="bg-blue-500 text-white py-2 px-4 rounded mt-4 cursor-pointer">
                            <a href="sitemap.php" class="delete bg-red-500 text-white py-2 px-4 rounded mt-4 cursor-pointer">Cancel</a>
                        </form>
                    <?php } else { ?>
                        <p class="mt-4">Invalid index provided.</p>
                    <?php }
                } else { ?>
                    <div id="add-switch-form" style="display:none;">
                        <form class="mt-4" method="POST">
                            <h2 class="text-xl font-semibold">Add Page to Sitemap</h2>
                            <label class="block mt-4">Page URL:</label>
                            <input type="text" name="page_url" class="w-full rounded border-gray-300 py-2 px-4" required>
                            <label class="block mt-4" style="display:none;">Last Modified:</label>
                            <input type="text" name="last_modified" value="<?php echo date('Y-m-d\TH:i:sP'); ?>" class="w-full rounded border-gray-300 py-2 px-4" style="display:none;" required>
                            <label class="block mt-4">Priority:</label>
                            <input type="number" name="priority" min="0" max="1" step="0.1" value="0.5" class="w-full rounded border-gray-300 py-2 px-4" required>
                            <input type="submit" name="submit" value="Add to Sitemap" class="bg-blue-500 text-white py-2 px-4 rounded mt-4 cursor-pointer">
                            <input id="close-switch-form-button" value="Close" type="submit" class="bg-red-500 text-white py-2 px-4 rounded mt-4 cursor-pointer">
                        </form>
                    </div>
                <?php } ?>

            </div>

            <table class="w-full bg-white shadow-md rounded-md overflow-x-auto mt-8">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Page URL</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Last Modified</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Priority</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($urls as $index => $url) { ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><a href="<?php echo $url->loc; ?>" target = "_blank" class="text-blue-500 hover:text-blue-700"><?php echo $url->loc; ?></a></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $url->lastmod; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $url->priority; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="sitemap.php?action=edit&index=<?php echo $index; ?>" class="edit bg-blue-500 text-white py-2 px-4 rounded cursor-pointer">Edit</a>
                                <a href="sitemap.php?action=delete&index=<?php echo $index; ?>" class="delete bg-red-500 text-white py-2 px-4 rounded cursor-pointer" onclick="return confirm('Are you sure you want to delete this page URL?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const addSwitchButton = document.querySelector('#add-switch-button');
        const addSwitchForm = document.querySelector('#add-switch-form');
        const closeSwitchFormButton = document.querySelector('#close-switch-form-button');
        addSwitchButton.addEventListener('click', function() {
            addSwitchForm.style.display = 'block';
        });
        closeSwitchFormButton.addEventListener('click', function() {
            addSwitchForm.style.display = 'none';
        });
    </script>
</body>
        <?php include 'includes/footer.php' ?>

</html>
