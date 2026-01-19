<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    echo "Unauthorized access.";
    exit();
}

$barangayId = $_SESSION['barangay_id'];

if (isset($_POST['delete_blotter'])) {
    $blotter_id = $_POST['blotter_id'];

    // Optional: Verify blotter belongs to barangay
    $checkStmt = $conn->prepare("SELECT barangay_id FROM incident_report WHERE id = ?");
    $checkStmt->bind_param("i", $blotter_id);
    $checkStmt->execute();
    $checkStmt->bind_result($blotterBarangayId);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($blotterBarangayId != $barangayId) {
        echo "You don't have permission to delete this record.";
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM incident_report WHERE id = ?");
    $stmt->bind_param("i", $blotter_id);

    if ($stmt->execute()) {
        echo "Blotter record deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
