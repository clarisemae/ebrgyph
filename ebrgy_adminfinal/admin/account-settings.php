<?php
session_start();
require_once './includes/access_control.php';

// Check for valid login and role
if (!isset($_SESSION['admin_id']) || 
    !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin']) || 
    ($_SESSION['role'] !== 'SuperAdmin' && !isset($_SESSION['barangay_id']))) {
    header("Location: admin_login.php");
    exit();
}

// Get the user data and profile picture
require 'includes/db_connection.php'; // Database connection
require 'includes/fetch_user.php';     // User data fetch function

$userId = $_SESSION['admin_id'];
$userData = fetchUserData($conn, $userId); // Fetch user data

// Check for success message and clear it after showing
$successMessage = $_SESSION['update_success'] ?? '';
unset($_SESSION['update_success']);

// Determine profile picture path, fallback to default image
$profilePicturePath = !empty($userData['profile_picture'])
    ? 'includes/uploads/' . htmlspecialchars($userData['profile_picture'])
    : 'images/profile.jpg';

// For SuperAdmin, you don't need a barangay selector
// They have access to all barangays, so we just proceed without showing a form.
$barangayId = $_SESSION['barangay_id']; // Admin/Staff use their own barangay_id
$selectedBarangay = $_SESSION['selected_barangay_id'] ?? $barangayId; // SuperAdmin can still use this for any selection if needed
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Account Settings</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="css/account-settings.css" />
</head>

<body>
    <header class="header">
        <div class="header-left">
        <a href="ebrgydashboard.php" class="back-to-dashboard">
        <span class="material-icons-outlined">arrow_back</span>
        </a>
        </div>
        <div class="header-right">
            <img src="<?= $profilePicturePath ?>" alt="Profile Picture" class="header-profile-photo" />
            <span><?= htmlspecialchars($userData['adminUsername'] ?? 'Guest') ?></span>
        </div>
    </header>

    <main class="account-settings-container">
        <h1>Account Settings</h1>

        <?php if (!empty($successMessage)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <!-- Account Settings Form -->
        <form action="includes/update_account.php" method="post" enctype="multipart/form-data" novalidate>
            <div class="form-group">
                <label for="profile_picture">Profile Picture:</label><br />
                <img src="<?= $profilePicturePath ?>" alt="Profile Picture" class="profile-photo" />
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" />
                <small>Upload a new profile picture. Leave blank to keep the current one.</small>
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="adminName" value="<?= htmlspecialchars($userData['adminName'] ?? '') ?>" required />
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="adminUsername" value="<?= htmlspecialchars($userData['adminUsername'] ?? '') ?>" required />
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required />
            </div>

            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" />
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" />
            </div>

            <button type="submit" name="updateSettings" class="btn">Update Settings</button>
        </form>
    </main>
</body>

</html>
