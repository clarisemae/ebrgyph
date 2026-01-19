document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault(); // Prevent the form from submitting

  const username = document.getElementById("adminUsername").value.trim(); // Get the username value
  const password = document.getElementById("password").value.trim(); // Get the password value

  // Error message container
  const errorMessage = document.getElementById("error-message");

  // Clear any previous error message
  errorMessage.style.display = "none";
  errorMessage.textContent = "";

  // Check if fields are empty
  if (username === "" || password === "") {
    errorMessage.style.display = "block";
    errorMessage.textContent = "Both fields are required. Please fill in all fields.";
    return; // Don't proceed further if fields are empty
  }

  // Retrieve stored users from localStorage (if using local storage)
  const storedUsers = JSON.parse(localStorage.getItem("users")) || [];

  // Find the user with the matching username and password
  const loggedInUser = storedUsers.find(user => user.username === username && user.password === password);

  // Validate credentials
  if (!loggedInUser) {
    errorMessage.style.display = "block";
    errorMessage.textContent = "Invalid username or password. Please try again.";
  } else {
    // Store the logged-in user's username in localStorage or sessionStorage (if required)
    localStorage.setItem("loggedInUsername", loggedInUser.username);

    alert("Login successful! Redirecting to the dashboard...");
    window.location.href = "residents.php"; // Redirect to dashboard
  }
});
