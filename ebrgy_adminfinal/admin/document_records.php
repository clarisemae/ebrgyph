<?php
session_start();
require_once './includes/access_control.php';
include './includes/db_connection.php';

// Check for valid login and role
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin'])) {
    header("Location: admin_login.php");
    exit();
}

// For SuperAdmin, use the selected barangay, otherwise use the regular barangay_id
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id']; // Use SuperAdmin's selected barangay if set

// If SuperAdmin selects a barangay, store it in the session
if ($_SESSION['role'] === 'SuperAdmin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector']; // Save selected barangay_id to session
    header("Location: document_records.php"); // Refresh the page to apply the new barangay
    exit();
}

// Get the list of barangays SuperAdmin can access
if ($_SESSION['role'] === 'SuperAdmin') {
    $barangayIds = $_SESSION['barangay_ids']; // Array of allowed barangays for SuperAdmin
    $selectedBarangay = $_SESSION['selected_barangay_id'] ?? $barangayIds[0]; // Default to first barangay if none selected
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="includes/general.css">
    <link rel="stylesheet" href="includes/header.css">
    <link rel="stylesheet" href="includes/sidebar.css">
    <link rel="stylesheet" href="css/document_records.css">
</head>
<body>
    <?php include('includes/header.php'); ?> <!-- Include Header -->
    <?php include('includes/sidebar.php'); ?> <!-- Include Sidebar -->

    <div class="main-container">
    <h1>Document Request Record</h1>

    <!-- Barangay Selector for SuperAdmin -->
    <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
        <form method="POST" action="">
            <label for="barangay_selector">Choose Barangay:</label>
            <select name="barangay_selector" id="barangay_selector" class="form-select mb-4 w-25" onchange="this.form.submit()">
                <?php
                // Fetch the list of barangays SuperAdmin can access
                foreach ($barangayIds as $barangay_id) {
                    $barangayName = "Barangay " . $barangay_id; // Replace with actual barangay name if needed
                    $selected = ($barangay_id == $selectedBarangay) ? "selected" : "";
                    echo "<option value='$barangay_id' $selected>$barangayName</option>";
                }
                ?>
            </select>
        </form>
    <?php endif; ?>

    <div class="document-table-container">
        <div class="row">
            <!-- Barangay Certificate -->
            <div class="card" data-document-type="barangay_certificate">
                <div>
                    <i class="fa-solid fa-certificate"></i>
                    <h5>Barangay Certificate</h5>
                    <p id="barangay-certificate-count">0</p>
                    <div class="line"></div>
                </div>
                <a href="admin_barangay_certificate.php" class="btn">More Info</a>
            </div>

            <!-- Certificate of Indigency -->
            <div class="card" data-document-type="certificate_of_indigency">
                <div>
                    <i class="fa-solid fa-certificate"></i>
                    <h5>Certificate of Indigency</h5>
                    <p id="certificate-of-indigency-count">0</p>
                    <div class="line"></div>
                </div>
                <a href="admin_certificate_of_indigency.php" class="btn">More Info</a>
            </div>

            <!-- COMELEC Registration -->
            <div class="card" data-document-type="certificate_of_comelec_registration">
                <div>
                    <i class="fa-solid fa-certificate"></i>
                    <h5>COMELEC Registration Certificate</h5>
                    <p id="comelec-registration-count">0</p>
                    <div class="line"></div>
                </div>
                <a href="admin_certificate_of_comelec_registration.php" class="btn">More Info</a>
            </div>

            <!-- Certificate of Non-Residency -->
            <div class="card" data-document-type="certificate_of_non_residency">
                <div>
                    <i class="fa-solid fa-certificate"></i>
                    <h5>Certificate of Non-Residency</h5>
                    <p id="certificate-of-non-residency">0</p>
                    <div class="line"></div>
                </div>
                <a href="admin_certificate_of_non_residency.php" class="btn">More Info</a>
            </div>

            <!-- Certificate of National ID -->
            <div class="card" data-document-type="certificate_of_national_id">
                <div>
                    <i class="fa-solid fa-certificate"></i>
                    <h5>Certificate of National ID</h5>
                    <p id="certificate-of-national-id-count">0</p>
                    <div class="line"></div>
                </div>
                <a href="admin_certificate_of_national_id.php" class="btn">More Info</a>
            </div>
        </div>
    </div>
    </div>

    <!-- Include JavaScript -->
    <script src="js/utils.js"></script>
    <script src="includes/header.js" type="module"></script>
    <script src="includes/sidebar.js" type="module"></script>
    <script src="fetchDoc/fetch_counts.js" type="module"></script>

    <script>
    // Fetch document counts using AJAX for SuperAdmin and Staff/Admin
    fetch('path/to/your/php/fetch_document_counts.php') // Update with the correct path to your PHP file
      .then(response => response.json())
      .then(data => {
        document.getElementById('barangay-certificate-count').textContent = data.barangay_certificate || 0;
        document.getElementById('certificate-of-indigency-count').textContent = data.certificate_of_indigency || 0;
        document.getElementById('comelec-registration-count').textContent = data.certificate_of_comelec_registration || 0;
        document.getElementById('certificate-of-non-residency').textContent = data.certificate_of_non_residency || 0;
        document.getElementById('certificate-of-national-id-count').textContent = data.certificate_of_national_id || 0;
      })
      .catch(error => {
        console.error('Error:', error);
      });
    </script>
</body>
</html>
