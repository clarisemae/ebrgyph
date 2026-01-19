<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    echo "Unauthorized: No barangay_id in session.";
    exit;
}

$barangay_id = $_SESSION['barangay_id'];

if (isset($_POST['delete_certificate'])) {
    $certificate_id = $_POST['certificate_id'];

    // Prepared statement with barangay check
    $stmt = $conn->prepare("DELETE FROM certificate_of_non_residency WHERE id = ? AND barangay_id = ?");
    $stmt->bind_param("ii", $certificate_id, $barangay_id);

    if ($stmt->execute()) {
        echo "Certificate of Non-Residency record deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
