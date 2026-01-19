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

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the user's profile picture
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $complainant = $_POST['complainant'];
    $accused = $_POST['accused'];
    $incident_type = $_POST['incident_type'];
    $incident_address = $_POST['incident_address'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $message = $_POST['message'];
    $incident_photo = NULL;

    // Handle file upload (if any)
    if (isset($_FILES['incident_photo']) && $_FILES['incident_photo']['error'] == 0) {
        $target_dir = "crudBlotter/uploads/"; // Specify the folder to save the uploaded files
        $target_file = $target_dir . basename($_FILES["incident_photo"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (getimagesize($_FILES["incident_photo"]["tmp_name"]) !== false) {
            if ($_FILES["incident_photo"]["size"] <= 5000000 && in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                if (move_uploaded_file($_FILES["incident_photo"]["tmp_name"], $target_file)) {
                    $incident_photo = $target_file; // Store the file path in the database
                }
            }
        }
    }

    // Insert the data into the database
    $sql = "INSERT INTO incident_report (complainant, accused, incident_type, incident_address, date, time, message, incident_photo, barangay_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $complainant, $accused, $incident_type, $incident_address, $date, $time, $message, $incident_photo, $barangay_id);

    if ($stmt->execute()) {
        header("Location: incidentsubmission.php");
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
    <title>Incident Report</title>
    <link rel="stylesheet" href="report.css" />
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
            <!-- Profile Circle -->
            <a href="profile.php" class="profile-circle">
                <img src="<?php echo $profile_picture; ?>" alt="User Profile" style="width:40px; height:40px; border-radius:50%;" />
            </a>
            <!-- Large Screen Logout Button -->
            <a href="logout.php" id="logout-btn-large" class="logout-large">Logout</a>
        </div>
    </div>

    <div class="form-container">
        <h2>Incident Report</h2>
        <form action="report.php" method="POST" enctype="multipart/form-data">
            <div class="input-item">
                <label for="complainant">Complainant:</label>
                <input type="text" id="complainant" name="complainant" class="input-field" required placeholder="Enter complainant name" />
            </div>
            <div class="input-item">
                <label for="accused">Accused/Subject:</label>
                <input type="text" id="accused" name="accused" class="input-field" required placeholder="Enter accused or subject name" />
            </div>
            <div class="input-item">
                <label for="incident-type">Incident Nature Type:</label>
                <select id="incident-type" name="incident_type" class="input-field" required>
                    <option value="" disabled selected>Select incident type</option>
                    <option value="Theft">Theft</option>
                    <option value="Vandalism">Vandalism</option>
                    <option value="Harassment">Harassment</option>
                    <option value="Assault">Assault</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="input-item">
                <label for="incident-address">Incident Address:</label>
                <input type="text" id="incident-address" name="incident_address" class="input-field" required placeholder="Enter incident address" />
            </div>
            <div class="input-item">
                <label for="date">Date of Incident:</label>
                <input type="date" id="date" name="date" class="input-field" required />
            </div>
            <div class="input-item">
                <label for="time">Time of Incident:</label>
                <input type="time" id="time" name="time" class="input-field" required />
            </div>
            <div class="input-item">
                <label for="message">Description of Incident:</label>
                <textarea id="message" name="message" class="input-field" rows="4" required placeholder="Describe the incident..."></textarea>
            </div>
            <div class="input-item">
                <label for="incident-photo">Attach Photo (if any):</label>
                <input type="file" id="incident-photo" name="incident_photo" accept="image/*" class="input-field" />
            </div>
            <button type="submit" class="btn">Submit Report</button>
        </form>
    </div>
</body>

</html>