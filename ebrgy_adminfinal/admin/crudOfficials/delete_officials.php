<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    echo "Unauthorized access.";
    exit;
}

$barangayId = $_SESSION['barangay_id'];

if (isset($_POST['delete_official'])) {
    $official_id = $_POST['official_id'];

    $stmt = $conn->prepare("DELETE FROM barangay_officials WHERE id = ? AND barangay_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $official_id, $barangayId);

        if ($stmt->execute()) {
            echo "Official deleted successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing the SQL statement: " . $conn->error;
    }
}
?>
