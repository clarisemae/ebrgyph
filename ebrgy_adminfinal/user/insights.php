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
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch the user's profile picture dynamically
$username = $_SESSION['username'];
$sql_users = "SELECT id FROM users WHERE username = ?";
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
$stmt_users->close();

// Fetch profile picture from barangay_registration table
$sql_registration = "SELECT profile_picture FROM barangay_registration WHERE id = ?";
$stmt_registration = $conn->prepare($sql_registration);
$stmt_registration->bind_param("i", $user_id);
$stmt_registration->execute();
$result_registration = $stmt_registration->get_result();
$registration_data = $result_registration->fetch_assoc();
$profile_picture = !empty($registration_data['profile_picture']) ? "uploads/" . $registration_data['profile_picture'] : "default.jpg";
$stmt_registration->close();

// Fetch barangay_id of the current user
$barangay_id = null;
$sql_user_barangay = "SELECT barangay_id FROM users WHERE id = ?";
$stmt_user_barangay = $conn->prepare($sql_user_barangay);
$stmt_user_barangay->bind_param("i", $user_id);
$stmt_user_barangay->execute();
$result_user_barangay = $stmt_user_barangay->get_result();
if ($row = $result_user_barangay->fetch_assoc()) {
    $barangay_id = $row['barangay_id'];
}
$stmt_user_barangay->close();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize inputs
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $date = $_POST['date'] ?? '';
    $type = htmlspecialchars(trim($_POST['type'] ?? ''));
    $comment = htmlspecialchars(trim($_POST['comment'] ?? ''));

    // Validate required fields
    if (empty($name) || !$email || empty($date) || empty($type) || empty($comment)) {
        die("All fields are required and must be valid.");
    }

    // Validate the date format (optional)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        die("Invalid date format. Use YYYY-MM-DD.");
    }

    // Use prepared statements to insert into the database
    $sql = "INSERT INTO insights (name, email, date, type, comment, barangay_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("sssssi", $name, $email, $date, $type, $comment, $barangay_id);

    if ($stmt->execute()) {
        // Redirect to a confirmation page after successful submission
        header("Location: insightsubmission.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Insights</title>
    <link rel="stylesheet" href="insights.css" />
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

        <!-- Large Screen Logout Button -->
        <div class="navbar-right">
            <a href="profile.php" class="profile-circle">
                <img src="<?php echo $profile_picture; ?>" alt="User Profile" style="width:40px; height:40px; border-radius:50%;" />
            </a>
            <a href="logout.php" id="logout-btn-large" class="logout-large">Logout</a>
        </div>
    </div>

    <div class="form-container">
        <h2>Resident's Insight</h2>
        <form action="insights.php" method="POST">
            <div class="input-item">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name" required />
            </div>

            <div class="input-item">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="ex.juandelacruz@gmail.com" required />
            </div>

            <div class="input-item">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required />
            </div>

            <div class="input-item">
                <label for="type">Insight About:</label>
                <select id="type" name="type" required>
                    <option value="" disabled selected>Select Type</option>
                    <option value="Barangay">Barangay Services</option>
                    <option value="App">The App</option>
                </select>
            </div>

            <div class="input-item">
                <label for="comment">Your Comment or Suggestion</label>
                <textarea id="comment" name="comment" placeholder="Write your insights here..." rows="5" required></textarea>
            </div>

            <button type="submit" class="btn">Submit</button>
        </form>
    </div>
</body>

</html>