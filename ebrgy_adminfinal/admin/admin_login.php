<?php
// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "ebrgyph";
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Initialize variables
$error_message = ""; 
$adminUsername = $password = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUsername = trim($_POST['adminUsername']);
    $password = $_POST['password'];

    if (empty($adminUsername) || empty($password)) {
        $error_message = "Please fill in both fields.";
    } else {
        // Fetch admin info including barangay_id (single) and role
        $sql = "SELECT Id, adminUsername, password, adminName, role, barangay_id FROM admin WHERE adminUsername = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $adminUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Set session data
                $_SESSION['admin_id'] = $user['Id'];
                $_SESSION['adminUsername'] = $user['adminUsername'];
                $_SESSION['admin_name'] = $user['adminName'];
                $_SESSION['role'] = $user['role'];

                // For SuperAdmin, check for multiple barangay access
                if ($user['role'] === 'SuperAdmin') {
                    $_SESSION['barangay_id'] = null; // No single barangay restriction

                    // Fetch allowed barangays for SuperAdmin from the new table
                    $barangayIdsSql = "SELECT barangay_id FROM superadmin_barangays WHERE admin_id = ?";
                    $barangayIdsStmt = $conn->prepare($barangayIdsSql);
                    $barangayIdsStmt->bind_param("i", $user['Id']);
                    $barangayIdsStmt->execute();
                    $barangayIdsResult = $barangayIdsStmt->get_result();

                    $barangayIds = [];
                    while ($row = $barangayIdsResult->fetch_assoc()) {
                        $barangayIds[] = $row['barangay_id']; // Collect allowed barangays
                    }

                    $_SESSION['barangay_ids'] = $barangayIds; // Store allowed barangays in session
                } else {
                    // For Staff/Admin: Store single barangay_id
                    $_SESSION['barangay_id'] = $user['barangay_id'];
                    $_SESSION['barangay_ids'] = null; // Not needed for non-SuperAdmin
                }

                // DEBUGGING: Check if session variables are set correctly
                echo "<pre>";
                print_r($_SESSION); // Print session data for debugging
                echo "</pre>";
                
                // Redirect to dashboard
                header("Location: ebrgydashboard.php");
                exit();
            } else {
                $error_message = "Incorrect password!";
            }
        } else {
            $error_message = "No user found with that username!";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Log In</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/login.css" />
</head>
<body>
    <div id="loading-overlay">
        <div class="loading-spinner"></div>
    </div>
    <header class="top-bar">
        <div class="top-bar-links">
            <a href="admin_login.php">Log In</a>
        </div>
    </header>
    <div class="login-container">
        <div class="login-logo">
            <img src="images/logo.png" alt="Barangay Logo" />
        </div>
        <h2 class="barangay-title">Sangguniang Barangay 834 Zone 91</h2>
        <form class="login-form" id="loginForm" method="POST" action="admin_login.php">
            <div class="form-group">
                <label for="adminUsername">Username:</label>
                <input
                    type="text"
                    id="adminUsername"
                    name="adminUsername"
                    placeholder="Enter your username"
                    value="<?php echo htmlspecialchars($adminUsername); ?>"
                    required
                />
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                />
            </div>
            <?php if (!empty($error_message)) { ?>
                <div id="error-message" style="color: red; margin-bottom: 10px;">
                    <?php echo $error_message; ?>
                </div>
            <?php } ?>
            <button type="submit" class="login-button">Log In</button>
        </form>
    </div>
</body>
</html>
