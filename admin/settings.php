<?php
  session_start();
  require_once 'includes/conn.php';

  // Check if user is logged in
  if (!isset($_SESSION['admin_username'])) {
    header("Location: login");
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>pCloud | Settings</title>
  <?php include 'includes/header.php'; ?>
  <style>
    .settings-container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #fff;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .toggle-section,
    .logout-section {
      margin-bottom: 20px;
    }

    .toggle-section h2,
    .logout-section h2 {
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
      margin-bottom: 10px;
    }

    .toggle-label {
      display: flex;
      align-items: center;
    }
  </style>
</head>

<body>
  <div class="flex">
    <?php include 'includes/nav.php'; ?>
    <main class="flex-grow p-8">
      <div class="py-16 px-10 settings-container">
        <h1 class="text-4xl text-black font-semibold mb-8">Settings</h1>

        <!-- Toggle Switch Section -->
        <div class="toggle-section">
        <div class="mb-8">
          <h2 class="text-2xl font-semibold mb-4">Toggle Dark Mode</h2>
          <label for="toggle-check" class="block text-sm font-bold mb-2">Enable Dark Mode:</label>
          <input type="checkbox" id="toggle-check" checked>
          <label for="toggle-check" class="toggle-label cursor-pointer">
            <div class="toggle-container">
              <div class="toggle-light-icon icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
              </div>
              <div class="toggle-dark-icon icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
              </div>
              <div class="toggle-circle"></div>
            </div>
          </label>
        </div>
        </div>

        <!-- Logout Section -->
        <div class="logout-section">
          <h2 class="text-2xl font-semibold mb-4">Logout</h2>
          <form action="logout.php" method="post">
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:bg-red-600">
              Logout
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</body>

</html>
