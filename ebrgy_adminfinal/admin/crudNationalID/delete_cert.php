<?php
session_start();
include '../includes/db_connection.php';

if (isset($_POST['delete_certificate'])) {
    if (!isset($_SESSION['barangay_id'])) {
        echo "No barangay_id in session.";
        exit;
    }
    $barangay_id = $_SESSION['barangay_id'];
    $certificate_id = $_POST['certificate_id'];

    // Optional: Only delete if the certificate belongs to the user's barangay
    $stmt = $conn->prepare("DELETE FROM certificate_of_national_id WHERE id = ? AND barangay_id = ?");
    $stmt->bind_param("ii", $certificate_id, $barangay_id);

    if ($stmt->execute()) {
        echo "Certificate of National ID record deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
