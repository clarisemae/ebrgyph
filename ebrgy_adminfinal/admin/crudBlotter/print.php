<?php
include '../includes/db_connection.php';

if (isset($_GET['blotter_id'])) {
    $blotter_id = intval($_GET['blotter_id']);
    $query = "SELECT * FROM incident_report WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $blotter_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $blotter = $result->fetch_assoc();
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Blotter Report</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    line-height: 1.6;
                }
                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid black;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                .header img {
                    width: 100px;
                    height: auto;
                }
                .center-content {
                    text-align: center;
                }
                .center-content h2, .center-content h3 {
                    margin: 5px 0;
                }
                .content {
                    margin: 20px 0;
                }
                .content h3 {
                    text-align: center;
                    text-decoration: underline;
                    margin-bottom: 20px;
                }
                .details p {
                    margin: 5px 0;
                }
                .footer {
                    margin-top: 30px;
                    text-align: right;
                }
                @media print {
                    body {
                        margin: 0;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="../images/logo-left.png" alt="Barangay Logo">
                <div class="center-content">
                    <h2>REPUBLIC OF THE PHILIPPINES</h2>
                    <h3>City of Manila</h3>
                    <h3>OFFICE OF THE PUNONG BARANGAY</h3>
                    <h4>BARANGAY 834 ZONE 91 / DISTRICT VI</h4>
                    <h4>PANDACAN, MANILA</h4>
                </div>
                <img src="../images/logo-right.png" alt="City Logo">
            </div>

            <div class="content">
                <h3>Blotter Form</h3>
                <p>Petsa ng Pagblotter: <?= htmlspecialchars($blotter['date']) ?></p>

                <div class="content">
                    <div class="section">
                        <label>A. Pangalan ng Nag-blo-blotter/Nagrereklamo:</label>
                        <input type="text" value="<?= htmlspecialchars($blotter['complainant']) ?>" readonly>
                        <label>Tirahan:</label>
                        <input type="text" value="<?= htmlspecialchars($blotter['incident_address']) ?>" readonly>
                    </div>

                    <div class="section">
                        <label>B. Pangalan ng Inirereklamo:</label>
                        <input type="text" value="<?= htmlspecialchars($blotter['accused']) ?>" readonly>
                    </div>

                    <div class="section">
                        <label>C. Reklamo:</label>
                        <input type="text" value="<?= htmlspecialchars($blotter['incident_type']) ?>" readonly>
                    </div>

                    <div class="section">
                        <label>D. Kaganapan ng Pangyayari:</label>
                        <p>Petsa: <?= htmlspecialchars($blotter['date']) ?></p>
                        <p>Oras: <?= htmlspecialchars($blotter['time']) ?></p>
                    </div>

                    <div class="section lines">
                    <label>E. Salaysay:</label>
                    <textarea rows="6" readonly><?= htmlspecialchars($blotter['message']) ?></textarea>
                </div>
            </div>

            <div class="signature">
                <div>
                    <p>Lagda ng Sumulat ng Salaysay:</p>
                    ___________________________
                </div>
                <div>
                    <p>Pangalan ng Barangay Desk Officer:</p>
                    ___________________________
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Blotter record not found.";
    }

    $stmt->close();
} else {
    echo "No blotter ID specified.";
}

if ($result->num_rows === 0) {
    echo "<p>No blotter record found with the provided ID.</p>";
    exit;
}


$conn->close();
?>