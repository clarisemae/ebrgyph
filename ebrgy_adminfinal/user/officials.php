<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ebrgyph";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch user's profile picture and barangay_id
$username = $_SESSION['username'];
$sql_users = "SELECT id, barangay_id FROM users WHERE username = ?";
$stmt_users = $conn->prepare($sql_users);
if (!$stmt_users) {
  die("Query preparation failed: " . $conn->error);
}
$stmt_users->bind_param("s", $username);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$user_data = $result_users->fetch_assoc();
if (!$user_data) {
  die("No user data found for username: " . htmlspecialchars($username));
}
$user_id = $user_data['id'];
$barangay_id = $user_data['barangay_id'];
$stmt_users->close();

// Fetch profile picture from barangay_registration table
$sql_registration = "SELECT profile_picture FROM barangay_registration WHERE id = ?";
$stmt_registration = $conn->prepare($sql_registration);
if (!$stmt_registration) {
  die("Query preparation failed: " . $conn->error);
}
$stmt_registration->bind_param("i", $user_id);
$stmt_registration->execute();
$result_registration = $stmt_registration->get_result();
$registration_data = $result_registration->fetch_assoc();
$profile_picture = !empty($registration_data['profile_picture']) ? "uploads/" . $registration_data['profile_picture'] : "default.jpg";
$stmt_registration->close();

// Fetch barangay officials data for the user's barangay_id
$sql_officials = "SELECT photo, name, role FROM barangay_officials WHERE barangay_id = ?";
$stmt_officials = $conn->prepare($sql_officials);
if (!$stmt_officials) {
  die("Query preparation failed: " . $conn->error);
}
$stmt_officials->bind_param("i", $barangay_id);
$stmt_officials->execute();
$result_officials = $stmt_officials->get_result();

$officials = [];
while ($row = $result_officials->fetch_assoc()) {
  $officials[] = $row; // Store all officials in an array
}
$stmt_officials->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Barangay Officials</title>
  <link rel="stylesheet" href="officials.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@800&display=swap" rel="stylesheet" />
</head>

<body>
  <div class="navbar">
    <div class="navbar-left">
      <a href="#ebrgy" id="ebrgy-link">e-brgyPH</a>
    </div>

    <div class="navbar-center">
      <a href="home.php" class="nav-link">Home</a>
      <a href="request_general.php" class="nav-link">Document Request</a>
      <a href="report.php" class="nav-link">Incident Report</a>
      <a href="officials.php" class="nav-link active">Barangay Officials</a>
      <a href="insights.php" class="nav-link">Comments/Insights</a>
    </div>

    <div class="navbar-right">
      <a href="profile.php" class="profile-circle">
        <img src="<?php echo $profile_picture; ?>" alt="User Profile"
          style="width:40px; height:40px; border-radius:50%;" />
      </a>
      <a href="logout.php" id="logout-btn-large" class="logout-large">Logout</a>
    </div>
  </div>

  <div class="header">
    <img src="logo.png" alt="Logo" />
    <div class="sangunian">
      <h1>Sangguniang Barangay <?php echo htmlspecialchars($barangay_id); ?> Zone 91</h1>
      <h2>Barangay Officials</h2>
    </div>
  </div>

  <div class="officials-container">
    <?php if (!empty($officials)) : ?>
      <?php foreach (array_chunk($officials, 3) as $row) : ?>
        <div class="official-row">
          <?php foreach ($row as $official) : ?>
            <div class="official-card">
              <?php
              // Prepend "barangay-officials/" to the stored path and verify if it exists
              $photo_path = "barangay-officials/" . htmlspecialchars($official['photo']);
              if (!file_exists($photo_path)) {
                $photo_path = "default.jpg"; // Use a default image if photo doesn't exist
              }
              ?>
              <img src="<?php echo $photo_path; ?>" alt="Official Photo" class="official-img" />
              <p class="official-name"><?php echo htmlspecialchars($official['name']); ?></p>
              <p class="official-position"><?php echo htmlspecialchars($official['role']); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php else : ?>
      <p>No officials found.</p>
    <?php endif; ?>
  </div>
</body>

</html>