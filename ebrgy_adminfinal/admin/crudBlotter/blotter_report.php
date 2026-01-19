<?php
// Step 1: Database connection
$conn = new mysqli("localhost", "root", "", "ebrgyph");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Fetch data using the ID from URL
$id = isset($_GET['blotter_id']) ? intval($_GET['blotter_id']) : 2;

if ($id <= 0) {
    http_response_code(400); // Send a 400 Bad Request status
    echo "<h3>Error: Invalid or missing ID. Please check your input.</h3>";
    exit;
}


$sql = "SELECT * FROM incident_report WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Step 3: Check if data exists
$blotter = $result->fetch_assoc();
if (!$blotter) {
    die("No blotter record found for the given ID.");
}

// Step 4: Close database connection
$conn->close();
?>



      <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blotter Report</title>
    <link rel="stylesheet" href="../css/blotter_report.css">
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="left-logo"> <img src="../images/logo.png" alt="Barangay Logo"></div>
       
        <div class="center-content">

            <img src="../images/2.png" alt="Top Logo" class="top-logo">

            <h3>REPUBLIC OF THE PHILIPPINES</h3>
            <h3>City of Manila</h3>
            <h2>BARANGAY 834 ZONE 91 / DISTRICT VI</h2>
            <h2>PANDACAN, MANILA</h2>
            <h2>CONTACT NO.: 8256 3089</h2>
        </div>
       <div class="right-logo"><img src="../images/1.png" alt="City Logo"></div> 
    </div>

    <!-- Content Section -->
    <div class="content">
        <h3>Blotter Form</h3>
        <p><strong>Petsa ng Pagblotter:</strong> <?= htmlspecialchars($blotter['date']) ?></p>

        <div class="section">
            <p><strong>A. Pangalan ng Nag-blo-blotter/Nagrereklamo:</strong></p>
            <input type="text" value="<?= htmlspecialchars($blotter['complainant']) ?>" readonly><br>

            <p><strong>Tirahan:</strong></p>
            <input type="text" value="<?= htmlspecialchars($blotter['incident_address']) ?>" readonly><br>

            <p><strong>B. Pangalan ng Inirereklamo:</strong></p>
            <input type="text" value="<?= htmlspecialchars($blotter['accused']) ?>" readonly><br>

            <p><strong>C. Reklamo:</strong></p>
            <input type="text" value="<?= htmlspecialchars($blotter['incident_type']) ?>" readonly><br>

            <p><strong>D. Kaganapan ng Pangyayari:</strong></p>
            <p><strong>Petsa:</strong> <?= htmlspecialchars($blotter['date']) ?></p>
            <p><strong>Oras:</strong> <?= htmlspecialchars($blotter['time']) ?></p>
        </div>

        <div class="section-lines">
            <p><strong>E. Salaysay:</strong></p>
            <textarea rows="6" readonly><?= htmlspecialchars($blotter['message']) ?></textarea>
        </div>

        <!-- Signature Section -->
        <div class="signature">
            <p><strong>Lagda ng Sumulat ng Salaysay:</strong></p>
            <p>___________________________</p><br>

            <p><strong>Pangalan ng Barangay Desk Officer:</strong></p>
            <p>___________________________</p>
        </div>
    </div>
</body>
</html>