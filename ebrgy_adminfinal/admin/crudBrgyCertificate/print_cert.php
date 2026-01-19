<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "ebrgyph");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data using the ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "<h3>Error: Invalid or missing ID. Please check your input.</h3>";
    exit;
}

// Prepare the SQL statement
$sql = "SELECT * FROM barangay_certificate WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Check if data exists
$document = $result->fetch_assoc();
if (!$document) {
    die("No record found for the given ID.");
}

// Close database connection
$conn->close();

// Parse the document requirements from the barangay_certificate_purposec field
$requirements = array_map('trim', explode(',', $document['barangay_certificate_purpose']));

function addOrdinalNumberSuffix($num) {
    if (!in_array(($num % 100), array(11,12,13))){
        switch ($num % 10) {
            // Handle 1st, 2nd, 3rd
            case 1: return $num.'st';
            case 2: return $num.'nd';
            case 3: return $num.'rd';
        }
    }
    return $num.'th';
}

$date = new DateTime($document['created_at']);
$formattedDate = $date->format('Y-m-d');
$day = $date->format('j'); // Day without leading zeros
$month = $date->format('F'); // Full month name
$year = $date->format('Y'); // Year

$dayWithSuffix = addOrdinalNumberSuffix($day);
$readableDate = "{$dayWithSuffix} of {$month}, {$year}";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/cert_css/cert_baranggaycert.css">
    <title>Barangay Certificate</title>
</head>
<body>
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
        <h1 class="heading">BARANGGAY CERTIFICATE</h1>
        <div class="content">
        <div class="officials">
            <div class="officials-text">
                <p><strong>BARANGAY CHAIRMAN</strong></p>
                <br>
                <p>HON. RONNIE G. INSON</p>
                <br><br>
                <p><strong> BRGY. COUNCIL MEMBERS:</strong></p>
                <br>
                <p>HON. LEONARD DRANOEL F. LERAZAN</p>
                <br>
                <p>HON. MA. ASUNCION E. SORIANO</p>
                <br>
                <p>HON. EDITHA V. ORATE</p>
                <br>
                <p>HON. GEMMA L. FELIPE</p>
                <br>
                <p>HON. LOURDES N. ROMERO</p>
                <br>
                <p>HON. MARIANITO L. BATILES JR.</p>
                <br>
                <p>HON. CHRISTIAN S. ROMERO</p>
                <br><br>
                <p><strong>SK CHAIRMAN</strong></p>
                <br>
                <p>HON. YASMAINE ABIGAIL P. MARAÑA</p>
                <br><br>
                <p><strong>BARANGAY SECRETARY</strong></p>
                <br>
                <p>MAY ANN T. MARCELINO</p>
                <br><br>
                <p><strong>BARANGAY TREASURER</strong></p>
                <br>
                <p>MARIAN R. ESQUIVEL</p>
                <br><br><br>
                <p class="invalid-notice">NOT VALID WITHOUT OFFICIAL BARANGAY SEAL</p>
                </div>
            </div>
            <div class="content-text">
                <h1>TO WHOM IT MAY CONCERN:</h1>
                <div class="content-letter">
                <p>This is to certify that <strong><?=htmlspecialchars($document['fullname']); ?></strong>,<strong><?=htmlspecialchars($document['age']); ?></strong>,
                    <strong><?=htmlspecialchars($document['status']); ?></strong>,<strong><?=htmlspecialchars($document['citizen']); ?></strong>,is a bonafide resident 
                    of this barangay with postal address at <strong><?=htmlspecialchars($document['address']); ?></strong> and has no derogatory record filed in our Barangay Office.</p>
                </div>
                <div class="content-letter1">
                <p>This certification is hereby issued upon the request of the above-named individual in connection with the subject’s application for:</p>
                <div class="checklist">
                    <p><strong>[ <?= in_array('Identification ID/Government ID', $requirements) ? 'X' : ' ' ?> ]</strong> Identification ID/Government ID</p>
                    <p><strong>[ <?= in_array('School Admission/Requirement', $requirements) ? 'X' : ' ' ?> ]</strong> School Admission/Requirement</p>
                    <p><strong>[ <?= in_array('Requirement for Local Employment', $requirements) ? 'X' : ' ' ?> ]</strong> Requirement for Local Employment</p>
                    <p><strong>[ <?= in_array('Barangay Clearance/Proof of Residency', $requirements) ? 'X' : ' ' ?> ]</strong> Barangay Clearance/Proof of Residency</p>
                    <p><strong>[ <?= in_array('Others', $requirements) ? 'X' : ' ' ?> ]</strong> Others:</p>
                </div>
                </div>

               <div class="acknowledge"><p>Given this <strong><?= htmlspecialchars($readableDate); ?></strong> at Baranggay 834, Zone 91, Pandacan, CIty of Manila, Philippines.</p></div>
               <div class="signature"><p>_________________</p><p><strong>RONNIE G. INSON</strong></p><p>Punong Baranggay</p></div>
            </div>
    </div>

    
</body>
</html>