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

// Fetch username and profile picture
$username = $_SESSION['username'];
$sql_users = "SELECT id, username, barangay_id FROM users WHERE username = ?";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("s", $username);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$user_data = $result_users->fetch_assoc();
$user_id = $user_data['id'];
$barangay_id = $user_data['barangay_id'];
$stmt_users->close();

// Barangay logo selection
$barangay_logo = '';
if ($barangay_id == 1) {
    $barangay_logo = 'barangay_logo/barangay_834.png';
} elseif ($barangay_id == 2) {
    $barangay_logo = 'barangay_logo/barangay_835.png';
}

// Fetch profile picture
$sql_registration = "SELECT profile_picture FROM barangay_registration WHERE id = ?";
$stmt_registration = $conn->prepare($sql_registration);
$stmt_registration->bind_param("i", $user_id);
$stmt_registration->execute();
$result_registration = $stmt_registration->get_result();
$registration_data = $result_registration->fetch_assoc();
$profile_picture = !empty($registration_data['profile_picture']) ? "uploads/" . $registration_data['profile_picture'] : "default.jpg";
$stmt_registration->close();

// Fetch active announcements for user's barangay only
$sql_announcements = "SELECT title, date, description, image FROM announcement WHERE status = 'Active' AND barangay_id = ? ORDER BY date DESC";
$stmt_announcements = $conn->prepare($sql_announcements);
$stmt_announcements->bind_param("i", $barangay_id);
$stmt_announcements->execute();
$result_announcements = $stmt_announcements->get_result();

// Fetch barangay name
$barangay_name = '';
$sql_barangay = "SELECT name FROM barangays WHERE id = ?";
$stmt_barangay = $conn->prepare($sql_barangay);
$stmt_barangay->bind_param("i", $barangay_id);
$stmt_barangay->execute();
$result_barangay = $stmt_barangay->get_result();
if ($row = $result_barangay->fetch_assoc()) {
    $barangay_name = $row['name'];
}
$stmt_barangay->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link rel="stylesheet" href="home.css" />
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

        <!-- Burger Menu for small screens -->
        <div class="burger-menu" id="burger-menu" aria-label="Toggle menu" role="button" tabindex="0">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

            <!-- Burger Dropdown Menu (mobile only) -->
    <div class="burger-dropdown" id="burger-dropdown" style="display: none;">
        <a href="home.php" class="dropdown-link">Home</a>
        <a href="request_general.php" class="dropdown-link">Document Request</a>
        <a href="report.php" class="dropdown-link">Incident Report</a>
        <a href="officials.php" class="dropdown-link">Barangay Officials</a>
        <a href="insights.php" class="dropdown-link">Comments/Insights</a>
        <a href="logout.php" class="dropdown-logout">Logout</a>
    </div>


        <!-- Profile Picture and Logout (right side) -->
        <div class="navbar-right">
            <a href="profile.php" class="profile-circle" aria-label="User Profile">
                <img src="<?php echo $profile_picture; ?>" alt="User Profile" />
            </a>
            <a href="logout.php" id="logout-btn-large" class="logout-large">Logout</a>
        </div>
    </div>
    <div class="header">
        <div class="welcome">
            <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" style="width:150px; height:150px; border-radius:50%;" />
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
                <?php if ($barangay_name): ?>
                    <h2><?php echo htmlspecialchars($barangay_name); ?></h2>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($barangay_logo): ?>
            <img src="<?php echo $barangay_logo; ?>" alt="Barangay Logo" />
        <?php endif; ?>
    </div>

    <section class="feed">
        <div class="content">
            <h1>Announcements</h1>
            <div class="grid">
                <?php if ($result_announcements->num_rows > 0): ?>
                    <?php while ($announcement = $result_announcements->fetch_assoc()): ?>
                        <div>
                            <a href="#announcement-<?php echo htmlspecialchars($announcement['title']); ?>">
                                <p><?php echo htmlspecialchars($announcement['title']); ?></p>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No announcements available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="news">
            <?php
            $result_announcements->data_seek(0); // Reset result pointer to fetch data again
            while ($announcement = $result_announcements->fetch_assoc()):
            ?>
                <h1 id="announcement-<?php echo htmlspecialchars($announcement['title']); ?>"><?php echo htmlspecialchars($announcement['title']); ?></h1>
                <div class="newsfeed">
                    <img src="/ebrgy_adminfinal/admin/crudAnnouncement/uploads/<?php echo htmlspecialchars($announcement['image']); ?>" alt="<?php echo htmlspecialchars($announcement['title']); ?>" />
                    <p>
                        <b>Date:</b> <?php echo htmlspecialchars($announcement['date']); ?><br />
                        <?php echo nl2br(htmlspecialchars($announcement['description'])); ?>
                    </p>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <script>
        const burgerMenu = document.getElementById('burger-menu');
        const burgerDropdown = document.getElementById('burger-dropdown');

        burgerMenu.addEventListener('click', () => {
            if (burgerDropdown.style.display === 'flex') {
                burgerDropdown.style.display = 'none';
            } else {
                burgerDropdown.style.display = 'flex';
                burgerDropdown.style.flexDirection = 'column'; // ensure vertical layout
            }
        });

        // Optional: close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!burgerMenu.contains(event.target) && !burgerDropdown.contains(event.target)) {
                burgerDropdown.style.display = 'none';
            }
        });
    </script>
</body>

</html>
