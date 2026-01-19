<?php
include 'db_connection.php'; // Database connection file

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect to login page if not logged in
    exit;
}

// Fetch user data from the admins table
$userId = $_SESSION['admin_id'];
$sql = "SELECT adminUsername, profile_picture FROM admin WHERE id = ?"; // Ensure your table and column names are correct
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Resolve the profile picture path
$profilePicturePath = !empty($userData['profile_picture']) 
    ? 'includes/uploads/' . $userData['profile_picture'] 
    : 'images/profile.jpg'; // Default profile picture if none is uploaded

// Resolve the username, default to 'Guest' if not set
$username = !empty($userData['adminUsername']) ? $userData['adminUsername'] : 'Guest';
?>
<header class="header">
  <div class="header-left">
    <span 
      class="burger-icon material-icons-outlined" 
      id="hamburger-menu" 
      style="cursor: pointer;">
      menu
    </span>
  </div>
    <div class="header-right">
        <div class="admin-profile">
            <img src="<?= htmlspecialchars($profilePicturePath) ?>" alt="Profile Picture" class="profile-photo">
            <span id="admin-name" 
                  onclick="redirectToAccountSettings();" 
                  style="cursor:pointer;">
                <?= htmlspecialchars($username) ?>
            </span>
        </div>
    </div>
</header>
<script>
    function redirectToAccountSettings() {
        window.location.href = "account-settings.php"; // Ensure this path is correct for your settings page
    }
</script>
