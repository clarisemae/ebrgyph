<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    echo "Unauthorized access.";
    exit;
}

$barangayId = $_SESSION['barangay_id'];

if (isset($_POST['delete_resident'])) {
    $resident_id = $_POST['resident_id'];

    $stmt = $conn_residents->prepare("DELETE FROM resident WHERE resident_id = ? AND barangay_id = ?");
    $stmt->bind_param("ii", $resident_id, $barangayId);

    if ($stmt->execute()) {
        echo "Resident deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>