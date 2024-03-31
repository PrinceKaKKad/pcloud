const sidebarToggleBtn = document.querySelector('#sidebar-toggle');
const sidebar = document.querySelector('.sidebar');
const toggle = document.querySelector('#toggle-check');
const html = document.querySelector("html");
const logoImage = document.getElementById('logo-image');

// Function to set a cookie with a specific name, value, and expiration time
function setCookie(name, value, days) {
  var expires = "";
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + value + expires + "; path=/";
}

// Function to get the value of a cookie by its name
function getCookie(name) {
  var nameEQ = name + "=";
  var cookies = document.cookie.split(';');
  for (var i = 0; i < cookies.length; i++) {
    var cookie = cookies[i];
    while (cookie.charAt(0) === ' ') cookie = cookie.substring(1, cookie.length);
    if (cookie.indexOf(nameEQ) === 0) return cookie.substring(nameEQ.length, cookie.length);
  }
  return null;
}

// Function to handle updating the logo image based on the cookie value
function updateLogoImageFromCookie() {
  var cookieValue = getCookie("sidebar_state");
  if (cookieValue) {
    var [lightMode, isClosed] = cookieValue.split("-");
    html.classList.toggle("light", lightMode === "light");
    sidebar.classList.toggle("closed", isClosed === "closed");
    updateLogoImage();
  }
}

// Event listener for the sidebar toggle button
sidebarToggleBtn.addEventListener("click", function () {
  sidebar.classList.toggle("closed");
  updateLogoImage();
  saveStateToCookie();
});

// Event listener for the toggle switch
toggle.addEventListener("change", function (e) {
  html.classList.toggle("light");
  updateLogoImage();
  saveStateToCookie();
});

// Function to update the logo image based on the current state
function updateLogoImage() {
  var isLightMode = html.classList.contains('light');
  var isSidebarClosed = sidebar.classList.contains('closed');
  var logoSrc;

  // Base path for logo images
  var basePath = '../assets/images/logo/';

  if (isLightMode && isSidebarClosed) {
    logoSrc = basePath + 'logo-dark-small.svg';
  } else if (!isLightMode && isSidebarClosed) {
    logoSrc = basePath + 'logo-light-small.svg';
  } else if (isLightMode && !isSidebarClosed) {
    logoSrc = basePath + 'logo-dark-big.svg';
  } else {
    logoSrc = basePath + 'logo-light-big.svg';
  }

  logoImage.src = logoSrc;
}

// Function to save the current state to a cookie
function saveStateToCookie() {
  var lightMode = html.classList.contains('light') ? 'light' : 'dark';
  var isClosed = sidebar.classList.contains('closed') ? 'closed' : 'open';
  var state = lightMode + '-' + isClosed;
  setCookie("sidebar_state", state, 365 * 10); // Set cookie to expire in 10 years
}

// Initial setup: Update logo image based on the cookie value
updateLogoImageFromCookie();