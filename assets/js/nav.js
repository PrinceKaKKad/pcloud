  document.addEventListener('DOMContentLoaded', function () {
    // Get the current page name from the URL
    var currentPage = window.location.pathname.split('/').pop();

    // If the URL is empty or contains "/index", set currentPage to "home"
    if (!currentPage || currentPage === 'index') {
      currentPage = 'home';
    }

    // Remove 'active' class from all li elements
    var menuItems = document.querySelectorAll('.sidebar nav ul li');
    menuItems.forEach(function (item) {
      item.classList.remove('active');
    });

    // Add 'active' class to the li element based on the current page
    var activeMenuItem = document.getElementById('menu-id-' + currentPage);
    if (activeMenuItem) {
      activeMenuItem.classList.add('active');
    }
  });
