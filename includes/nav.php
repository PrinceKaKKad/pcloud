  <div class="sidebar flex flex-col antialiased relative shadow-xl">
    <a href="index" class="logo flex-shrink-0 m-4 w-70 h-20 flex justify-center items-center text-white hover:text-gray-200 transition duration-300">
      <img id="logo-image" src="assets/images/logo/logo-light-big.svg" alt="Logo">
    </a>
<!--     <div class="mx-4 search flex-shrink-0 relative h-14 transition duration-300">
      <div class="h-14 w-14 absolute z-10 grid place-items-center"><i icon-name="search"></i></div>
      <input type="text" class="pl-14 outline-none absolute top-0 left-0 w-full h-full rounded placeholder-current" placeholder="Search...">
    </div> -->
    <nav class="my-4 flex-1">
      <ul class="flex flex-col h-full">
        <li id="menu-id-home">
          <a href="index" class="mx-4 flex h-14 rounded items-center transition duration-300">
            <div class="p-4"><i icon-name="home"></i></div>
            <span class="font-semibold">Home</span>
          </a>
        </li>
        <li id="menu-id-messages">
          <a href="messages" class="mx-4 flex h-14 rounded items-center transition duration-300 relative">
            <div class="p-4"><i icon-name="mail"></i></div>
            <span class="font-semibold">Messages</span>
            <!-- <div class="indicator bg-red-400 w-1.5 h-1.5 rounded-full right-0 absolute transition duration-300"></div> -->
          </a>
        </li>
        <li id="menu-id-projects" class="active">
          <a href="projects" class="mx-4 flex h-14 rounded items-center transition duration-300">
            <div class="p-4"><i icon-name="inbox"></i></div>
            <span class="font-semibold">Projects</span>
          </a>
        </li>
        <li id="menu-id-reports">
          <a href="reports" class="mx-4 flex h-14 rounded items-center transition duration-300">
            <div class="p-4"><i icon-name="gauge"></i></div>
            <span class="font-semibold">Reports</span>
          </a>
        </li>
        <li id="menu-id-team">
          <a href="team" class="mx-4 flex h-14 rounded items-center transition duration-300">
            <div class="p-4"><i icon-name="users"></i></div>
            <span class="font-semibold">Team</span>
          </a>
        </li>
        <li id="menu-id-expenses">
          <a href="expenses" class="mx-4 flex h-14 rounded items-center transition duration-300">
            <div class="p-4"><i icon-name="wallet"></i></div>
            <span class="font-semibold">Expenses</span>
          </a>
        </li>
        <li id="menu-id-support" class="withDivider mt-4 pt-4">
          <a href="support" class="mx-4 flex h-14 rounded items-center transition duration-300 relative">
            <div class="p-4"><i icon-name="life-buoy"></i></div>
            <span class="font-semibold">Support</span>
            <?php
            if (isset($_SESSION['username'])) {

            $user_id = $_SESSION['user_id'];

            // Check if there are unseen messages for the user
            $stmt = $pdo->prepare("SELECT COUNT(*) AS unseen_count FROM support_messages WHERE user_id = ? AND seen = 0");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();

            // Check if there are any unseen messages
            if ($result['unseen_count'] > 0) {
                // Echo the indicator if there are unseen messages
                echo '<div class="indicator bg-blue-400 w-1.5 h-1.5 rounded-full right-0 absolute transition duration-300"></div>';
            }
          }
            ?>

            <!-- <div class="indicator bg-blue-400 w-1.5 h-1.5 rounded-full right-0 absolute transition duration-300"></div> -->
          </a>
        </li>
        <li id="menu-id-notifications" class="mt-auto">
          <a href="notifications" class="mx-4 flex h-14 rounded items-center transition duration-300 relative">
            <div class="p-4"><i icon-name="bell"></i></div>
            <span class="font-semibold">Notifications</span>
            <!-- <div class="indicator bg-blue-400 w-1.5 h-1.5 rounded-full right-0 absolute transition duration-300"></div> -->
          </a>
        </li>
        <li id="menu-id-settings">
          <a href="settings" class="mx-4 flex h-14 rounded items-center transition duration-300">
            <div class="p-4"><i icon-name="settings"></i></div>
            <span class="font-semibold">Settings</span>
          </a>
        </li>
      </ul>
    </nav>
    <div class="footer flex flex-shrink-0 px-4">
      <div class="px-4 flex items-center cursor-pointer icon transition duration-300">
        <i icon-name="briefcase"></i>
      </div>
      <div class="flex w-full briefcase relative transition duration-300">
        <div class="justify-center flex flex-col">
          <?php 
            if (isset($_SESSION['username'])) {
              echo '<a href="" class="whitespace-nowrap font-semibold">';
              echo $_SESSION['username']; 
              echo '</a>';
            }
            else{
              echo '<a href="login" class="whitespace-nowrap font-semibold">';
              echo 'Login';
              echo '</a>';
            }
          ?>
          <span class="text-sm">Workspace</span>
        </div>
        <div class="ml-auto cursor-pointer items-center more flex transition duration-300">
          <!-- <i icon-name="chevron-up"></i> -->
        </div>
      </div>
    </div>


    <div id="sidebar-toggle" class="transition duration-500 toggle flex w-8 h-8 items-center justify-center absolute rounded-full top-7 -right-4 z-10 cursor-pointer shadow">
      <i icon-name="chevron-left"></i>
    </div>
  </div>


<?php
// Get the current URL path
$currentPath = $_SERVER['REQUEST_URI'];

// Check if the path contains the word "settings"
if (strpos($currentPath, '/settings') === false) {
    // Add the line of code you want to conditionally include
    echo "<input type='checkbox' id='toggle-check'>";
}
?>