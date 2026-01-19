<?php
session_start();
include '../includes/db_connection.php';

$barangayId = $_SESSION['barangay_id'] ?? null;

if (isset($_POST['delete_certificate']) && $barangayId) {
    $certificate_id = $_POST['certificate_id'];

    // Check if the certificate belongs to this barangay
    $stmtCheck = $conn->prepare("SELECT barangay_id FROM certificate_of_indigency WHERE id = ?");
    $stmtCheck->bind_param("i", $certificate_id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($ownerBarangayId);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($ownerBarangayId != $barangayId) {
        echo "Unauthorized action.";
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM certificate_of_indigency WHERE id = ?");
    $stmt->bind_param("i", $certificate_id);

    if ($stmt->execute()) {
        echo "Certificate of Indigency record deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
