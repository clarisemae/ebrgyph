<?php
session_start();
require_once './includes/access_control.php';

if (isset($_SESSION['error_message'])) {
    echo '<div id="error-message" class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']); // Clear it after showing
}

// Check for valid login and role
if (!isset($_SESSION['admin_id']) || 
    !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin']) || 
    ($_SESSION['role'] !== 'SuperAdmin' && !isset($_SESSION['barangay_id']))) {
    header("Location: admin_login.php");
    exit();
}

// On form submit, store the selected barangay_id in the session for SuperAdmin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector']; // Save selected barangay_id to session
    header("Location: ebrgydashboard.php"); // Refresh the page to apply the new barangay selection
    exit();
}

// Get selected barangay for filtering
$selectedBarangay = $_SESSION['selected_barangay_id'] ?? null; // Default to null or previous selection

// Get the list of barangays the SuperAdmin has access to
$barangayIds = $_SESSION['barangay_ids']; // Array of allowed barangays for SuperAdmin
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Barangay Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/header.css">
  <link rel="stylesheet" href="includes/sidebar.css">
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<?php include('includes/header.php'); ?> <!-- Include Header -->
<?php include('includes/sidebar.php'); ?> <!-- Include Sidebar -->

<main class="main-container">

<div>
<h1>Dashboard</h1>

<?php if ($_SESSION['role'] === 'SuperAdmin') { ?>
    <!-- Barangay Selector for SuperAdmin -->
    <form method="POST" action="">
        <label for="barangay_selector">Choose Barangay:</label>
        <select name="barangay_selector" id="barangay_selector" class="form-select mb-4 w-25" onchange="this.form.submit()">
            <?php
            // Fetch the list of barangays SuperAdmin can access
            $barangayIds = $_SESSION['barangay_ids']; // Array of allowed barangays for SuperAdmin

            // Fetch barangay names (you may want to get actual names instead of IDs from the DB)
            foreach ($barangayIds as $barangay_id) {
                $barangayName = "Barangay " . $barangay_id; // Example; replace with actual name
                $selected = ($barangay_id == $_SESSION['selected_barangay_id']) ? "selected" : "";
                echo "<option value='$barangay_id' $selected>$barangayName</option>";
            }
            ?>
        </select>
    </form>
<?php } ?>

<div class="dashboard-container">
    <!-- Metric Cards -->
    <div class="row">
        <div class="col-md-6">
            <a href="residents.php" class="card-link">
                <div class="card p-3 text-center">
                    <i class="material-icons-outlined">group</i>
                    <h5>Total Residents</h5>
                    <p id="resident-count">0</p>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="blotter.php" class="card-link">
                <div class="card p-3 text-center">
                    <i class="material-icons-outlined">gavel</i>
                    <h5>Total Blotters</h5>
                    <p id="incident_report-count">0</p>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="announcements.php" class="card-link">
                <div class="card p-3 text-center">
                    <i class="material-icons-outlined">campaign</i>
                    <h5>Total Announcements</h5>
                    <p id="announcement-count">0</p>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="admin_insights.php" class="card-link">
                <div class="card p-3 text-center">
                    <i class="material-icons-outlined">insights</i>
                    <h5>Resident Insight</h5>
                    <p id="insights-count">0</p>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="mt-4">
        <h2>Statistics</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Document Requests</h5>
                    <canvas id="document-request-chart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Incident Types</h5>
                    <canvas id="incident-type-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="js/utils.js"></script>
<script src="includes/header.js" type="module"></script>
<script src="includes/sidebar.js" type="module"></script>
<script src="fetchDashboard/fetch_counts.js" type="module"></script>

<script>
setTimeout(() => {
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        errorMessage.style.transition = 'opacity 0.5s ease';
        errorMessage.style.opacity = '0';
        setTimeout(() => errorMessage.remove(), 250); // remove after fade out
    }
}, 2000); // 3000 milliseconds = 3 seconds
</script>

</body>
</html>
