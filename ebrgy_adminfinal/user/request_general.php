<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  header("Location: login.php"); // Redirect to login page if not logged in
  exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ebrgyph";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch username from users table
$username = $_SESSION['username'];
$sql_users = "SELECT id FROM users WHERE username = ?";
$stmt_users = $conn->prepare($sql_users);
if (!$stmt_users) {
  die("Query preparation failed (users): " . $conn->error);
}
$stmt_users->bind_param("s", $username);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$user_data = $result_users->fetch_assoc();
if (!$user_data) {
  die("No user data found in users table for username: " . htmlspecialchars($username));
}
$user_id = $user_data['id']; // Get user ID
$stmt_users->close();

// Fetch profile picture from barangay_registration table
$sql_registration = "SELECT profile_picture FROM barangay_registration WHERE id = ?";
$stmt_registration = $conn->prepare($sql_registration);
if (!$stmt_registration) {
  die("Query preparation failed (barangay_registration): " . $conn->error);
}
$stmt_registration->bind_param("i", $user_id);
$stmt_registration->execute();
$result_registration = $stmt_registration->get_result();
$registration_data = $result_registration->fetch_assoc();
$profile_picture = !empty($registration_data['profile_picture']) ? "uploads/" . $registration_data['profile_picture'] : "default.jpg";
$stmt_registration->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document Request</title>
  <link rel="stylesheet" href="request_general.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@800&display=swap" rel="stylesheet" />
</head>

<body>
  <div class="navbar">
    <!-- Left Section -->
    <div class="navbar-left">
      <a href="#ebrgy" id="ebrgy-link">e-brgyPH</a>
    </div>

    <!-- Center Navbar Links -->
    <div class="navbar-center">
      <a href="home.php" class="nav-link active">Home</a>
      <a href="request_general.php" class="nav-link">Document Request</a>
      <a href="report.php" class="nav-link">Incident Report</a>
      <a href="officials.php" class="nav-link">Barangay Officials</a>
      <a href="insights.php" class="nav-link">Comments/Insights</a>
    </div>

    <!-- Burger Menu -->
    <div class="burger-menu" id="burger-menu" aria-label="Toggle menu" aria-expanded="false">
      <div class="bar"></div>
      <div class="bar"></div>
      <div class="bar"></div>
    </div>

    <!-- Burger Dropdown Menu -->
    <div
      class="burger-dropdown"
      id="burger-dropdown"
      role="menu"
      aria-hidden="true">
      <a href="home.php" class="dropdown-link active">Home</a>
      <a href="request_general.php" class="dropdown-link">Document Request</a>
      <a href="report.php" class="dropdown-link">Incident Report</a>
      <a href="officials.php" class="dropdown-link">Barangay Officials</a>
      <a href="insights.php" class="dropdown-link">Comments/Insights</a>
      <a href="profile.php" class="dropdown-link">Profile</a>
      <!-- Logout Button -->
      <a href="logout.php" id="logout-btn" class="dropdown-logout">Logout</a>
    </div>

    <!-- Large Screen Logout Button -->
    <div class="navbar-right">
      <!-- Profile Circle -->
      <a href="profile.php" class="profile-circle">
        <img src="<?php echo $profile_picture; ?>" alt="User Profile"
          style="width:40px; height:40px; border-radius:50%;" />
      </a>
      <!-- Large Screen Logout Button -->
      <a href="logout.php" id="logout-btn-large" class="logout-large">Logout</a>
    </div>
  </div>

  <!-- Request Form -->
  <section id="document-request-form">
    <h2>Document Request</h2>
    <select id="document" name="document" required onchange="redirectToPage(this)">
      <option value="" disabled selected>Select Document Type</option>
      <option value="barangay_certificate">Barangay Certificate</option>
      <option value="certificate_of_indigency">Certificate of Indigency</option>
      <option value="certificate_of_comelec_registration">Certificate of COMELEC Registration</option>
      <option value="certificate_of_non_residency">Certificate of Non-Residency</option>
      <option value="certificate_of_national_id">Certificate of National ID</option>
    </select>
  </section>

  <script>
    function redirectToPage(selectElement) {
      var selectedValue = selectElement.value;

      // Redirect based on the selected value
      if (selectedValue) {
        window.location.href = selectedValue + '.php'; // Assuming each document type has its own HTML page
      }
    }
  </script>
  <script src="app.js"></script>
</body>

</html>