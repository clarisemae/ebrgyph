<?php
session_start();
include '../includes/db_connection.php';

$barangayId = $_SESSION['barangay_id'] ?? null;
if (!$barangayId) {
    die("Barangay ID missing in session.");
}

if (isset($_POST['delete_certificate'])) {
    $certificate_id = $_POST['certificate_id'];

    // Delete only if the record belongs to the user's barangay
    $stmt = $conn->prepare("DELETE FROM certificate_of_comelec_registration WHERE id = ? AND barangay_id = ?");
    $stmt->bind_param("ii", $certificate_id, $barangayId);

    if ($stmt->execute()) {
        echo "Certificate of Comelec Registration record deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
