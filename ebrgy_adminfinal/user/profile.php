<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM barangay_registration WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Query execution failed: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("No user data found for user ID: " . htmlspecialchars($user_id));
}

$user_data = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>

<body>
    <div class="navbar">
        <!-- Navbar Content -->
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

        <div class="burger-menu" id="burger-menu" aria-label="Toggle menu" aria-expanded="false">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        <div class="burger-dropdown" id="burger-dropdown" role="menu" aria-hidden="true">
            <a href="home.php" class="dropdown-link">Home</a>
            <a href="request_general.html" class="dropdown-link">Document Request</a>
            <a href="report.html" class="dropdown-link">Incident Report</a>
            <a href="officials.html" class="dropdown-link">Barangay Officials</a>
            <a href="insights.html" class="dropdown-link">Comments/Insights</a>
            <a href="profile.php" class="dropdown-link active">Profile</a>
            <a href="logout.php" class="dropdown-logout">Logout</a>
        </div>

        <div class="navbar-right">
            <a href="profile.php" class="profile-circle">
                <img src="uploads/<?php echo htmlspecialchars($user_data['profile_picture']); ?>" alt="User Profile" />
            </a>
            <a href="logout.php" id="logout-btn-large" class="logout-large">Logout</a>
        </div>
    </div>

    <div class="profile-container">
        <h2>Profile Information</h2>
        <div class="profile-picture">
            <img src="uploads/<?php echo htmlspecialchars($user_data['profile_picture'] ?? 'default.jpg'); ?>" alt="Profile Picture" width="150" height="150" style="border-radius: 50%;">
        </div>
        <div class="profile-details">
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user_data['full_name']); ?></p>
            <p><strong>Birthdate:</strong> <?php echo htmlspecialchars($user_data['birthdate']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($user_data['gender']); ?></p>
            <p><strong>Civil Status:</strong> <?php echo htmlspecialchars($user_data['civil_status']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_data['phone']); ?></p>
            <p><strong>Address:</strong>
                <?php
                echo htmlspecialchars(
                    $user_data['street'] . ', ' .
                        $user_data['barangay'] . ', ' .
                        $user_data['municipality'] . ', ' .
                        $user_data['city'] . ', ' .
                        $user_data['region']
                );
                ?>
            </p>
        </div>

        <h3>Emergency Contact Information</h3>
        <div class="emergency-details">
            <p><strong>Contact Person:</strong> <?php echo htmlspecialchars($user_data['emergency_name']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user_data['emergency_address']); ?></p>
            <p><strong>Relationship:</strong> <?php echo htmlspecialchars($user_data['emergency_relationship']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_data['emergency_phone']); ?></p>
        </div>

        <a href="edit_profile.php" class="btn">Edit Profile</a>
    </div>

    <script>
        // Burger menu functionality
        const burgerMenu = document.getElementById('burger-menu');
        const burgerDropdown = document.getElementById('burger-dropdown');

        burgerMenu.addEventListener('click', () => {
            const isExpanded = burgerMenu.getAttribute('aria-expanded') === 'true';
            burgerMenu.setAttribute('aria-expanded', !isExpanded);
            burgerDropdown.setAttribute('aria-hidden', isExpanded);
            burgerDropdown.style.display = isExpanded ? 'none' : 'block';
        });
    </script>
</body>

</html>