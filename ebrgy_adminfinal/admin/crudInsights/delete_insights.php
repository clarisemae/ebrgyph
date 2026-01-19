<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    echo "Unauthorized: No barangay_id in session.";
    exit();
}

$barangayId = $_SESSION['barangay_id'];

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Prepare statement to delete only if insight belongs to user's barangay
    $stmt = $conn->prepare("DELETE FROM insights WHERE id = ? AND barangay_id = ?");
    $stmt->bind_param("ii", $id, $barangayId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Insight deleted successfully.";
        } else {
            echo "No insight found with that ID in your barangay.";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
