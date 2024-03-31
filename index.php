<?php
  session_start();
  // Check if user is logged in
  if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit();
  }
  // Connect to database
  require_once('includes/conn.php');

  // Get user's templates
  $user_id = $_SESSION['user_id'];
  $stmt = $pdo->prepare("SELECT * FROM template WHERE user_id = ?");
  $stmt->execute([$user_id]);
  $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>pCloud | Home</title>
  <?php include 'includes/header.php' ?>
</head>

<body>
  <div class="flex">
    <?php include 'includes/nav.php' ?>
    <main>
      <div class="py-16 px-10">
        <h1 class="text-4xl text-black font-semibold mb-8">Home</h1>
        <div class="card-body">

          <div class="container">
            <div class="row text-light">
              <div class="row grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="popup hidden" id="copied-popup">Copied!</div>
                <?php if (empty($templates)): ?>
                <div class="col">
                  <div class="bg-gray-300 h-full text-light rounded-lg overflow-hidden shadow-md">
                    <div class="card-body p-6">
                      <a href="create_project" class="text-decoration-none">
                        <h5 class="card-title text-light text-xl font-semibold mb-2">No Projects are available.</h5>
                      </a>
                      <div class="flex justify-between mt-4">
                        <a href="create_project" class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600">Create new project</a>

                      </div>
                    </div>
                  </div>
                </div>
                <?php else: ?>
                <?php $i = 1; foreach ($templates as $template): ?>
                <div class="col">
                  <div class="bg-gray-300 h-full text-light rounded-lg overflow-hidden shadow-md">
                    <div class="card-body p-6">
                      <a href="projects?token=<?php echo $template['id']; ?>" class="text-decoration-none">
                        <h5 class="card-title text-light text-xl font-semibold mb-2"><?php echo $i; ?>. <?php echo $template['name']; ?></h5>
                      </a>
                      <div class="card-text overflow-hidden">
                        <b>Token:</b>
                        <span id="auth-<?php echo $i; ?>" onclick="copyToClipboard('auth-<?php echo $i; ?>')" class="cursor-pointer whitespace-no-wrap">
                          <?php echo $template['auth']; ?> <i class="fa fa-copy" style="color: grey"></i>
                        </span>
                      </div>
                      <div class="flex justify-between mt-4">
                        <a href="manage_projects?token=<?php echo $template['id']; ?>" class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600">Edit</a>
                        <a href="delete_projects?token=<?php echo $template['id']; ?>&auth=<?php echo $template['auth']; ?>" class="btn btn-danger bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-red-600" onclick="return confirm('Are you sure you want to delete this projects?')">Delete</a>
                      </div>
                    </div>
                  </div>
                </div>

                <?php $i++; endforeach; ?>
                <div class="col">
                  <div class="bg-gray-300 h-full text-light rounded-lg overflow-hidden shadow-md">
                    <div class="card-body p-6">
                      <a href="create_project" class="text-decoration-none">
                        <h5 class="card-title text-light text-xl font-semibold mb-2">Create new project</h5>
                      </a>
                      <div class="flex justify-between mt-4">
                        <a href="create_project" class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md focus:outline-none focus:bg-blue-600">New</a>

                      </div>
                    </div>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <script>
          function copyToClipboard(elementId) {
            var text = document.getElementById(elementId).innerText;
            var tempInput = document.createElement("input");
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            var popup = document.getElementById("copied-popup");
            popup.classList.add("show");
            setTimeout(function() {
              popup.classList.remove("show");
            }, 2000);
          }
        </script>

      </div>
    </main>

  </div>
  <?php include 'includes/footer.php' ?>
</body>

</html>