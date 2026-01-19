document.getElementById("signupForm").addEventListener("submit", function (e) {
  e.preventDefault(); // Prevent the form from refreshing the page

  // Get form values
  const adminName = document.getElementById("name").value.trim();  // adminName instead of name
  const adminUsername = document.getElementById("username").value.trim();  // adminUsername instead of username
  const email = document.getElementById("email").value.trim();
  const phone = document.getElementById("number").value.trim();
  const password = document.getElementById("password").value.trim();
  const confirmPassword = document.getElementById("confirm-password").value.trim();

  // Error message container
  const errorMessage = document.getElementById("error-message");

  // Validate that passwords match
  if (password !== confirmPassword) {
      errorMessage.style.display = "block";
      errorMessage.textContent = "Passwords do not match. Please try again.";
      return;
  }

  // Ajax request to check if username/email exists
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "signup.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
          const response = xhr.responseText;

          // Handle responses from PHP
          if (response.includes("Username is already taken")) {
              alert("Username is already taken. Please choose a different username.");
          } else if (response.includes("Email is already registered")) {
              alert("Email is already registered. Please use a different email.");
          } else if (response.includes("Passwords do not match")) {
              alert("Passwords do not match. Please check your password and try again.");
          } else if (response.includes("Account successfully created")) {
              alert("Account created successfully!");
              window.location.href = "login.php"; // Redirect to login page
          } else {
              alert("Something went wrong. Please try again.");
          }
      }
  };

  // Send form data to the PHP script
  xhr.send(`adminName=${adminName}&adminUsername=${adminUsername}&email=${email}&number=${phone}&password=${password}&confirm-password=${confirmPassword}`);
});
