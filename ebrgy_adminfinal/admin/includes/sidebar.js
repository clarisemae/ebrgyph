  // Sidebar Toggle
  const hamburgerMenu = document.getElementById("hamburger-menu");
  const sidebar = document.getElementById("sidebar");
  const mainContainer = document.querySelector(".main-container");
  const header = document.querySelector("header.header");

  hamburgerMenu.addEventListener("click", function () {
    sidebar.classList.toggle("open");
    mainContainer.classList.toggle("sidebar-open");
    header.classList.toggle("sidebar-open");
  });

  
// Log Out Function
function logOut() {
  // Clear stored admin data
  localStorage.removeItem("loggedInUsername");
  localStorage.removeItem("adminName");
  localStorage.removeItem("adminPassword");

  // Redirect to login page
  window.location.href = "admin_login.php";
}

// Add event listener to all sidebar links
document.querySelectorAll('.sidebar-menu li a').forEach(link => {
  link.addEventListener('click', function (event) {
      event.preventDefault(); // Prevent immediate navigation

      const targetUrl = this.href; // Get the link URL

      // Add fade-out class to body
      document.body.classList.add('fade-out');

      // Navigate to the new page after the fade-out animation
      setTimeout(() => {
          window.location.href = targetUrl;
      }, 500); // Match the duration of the CSS transition
  });
});
