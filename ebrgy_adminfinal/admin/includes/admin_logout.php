<?php
session_start(); // Start the session to access session variables

// Check if the user has confirmed the logout via a query parameter
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Unset only user-specific session variables
    unset($_SESSION['user_id']);
    unset($_SESSION['user_username']);

    // Optionally, add a message for user logout
    $_SESSION['message'] = "You have been logged out successfully.";

    // Redirect the user to the user login page
    header("Location: admin_login.php"); // Redirect to login page
    exit(); // Stop script execution
} else {
    // If the user hasn't confirmed, show a confirmation prompt using JavaScript
    echo '<script>
        var userConfirmed = confirm("Are you sure you want to log out?");
        if (userConfirmed) {
            // If confirmed, redirect to logout page with confirmation query parameter
            window.location.href = "logout.php?confirm=yes";
        } else {
            // If canceled, redirect back to the home page or any other page
            window.location.href = "home.php";
        }
    </script>';
}
