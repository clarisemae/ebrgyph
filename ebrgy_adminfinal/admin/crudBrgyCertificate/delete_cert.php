<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    die("Barangay not set in session.");
}

$barangay_id = $_SESSION['barangay_id'];

if (isset($_POST['certificate_id'])) {
    $certificate_id = intval($_POST['certificate_id']);

    $stmt = $conn->prepare("DELETE FROM barangay_certificate WHERE id = ? AND barangay_id = ?");
    $stmt->bind_param("ii", $certificate_id, $barangay_id);

    if ($stmt->execute()) {
        echo "Barangay Certificate record deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
