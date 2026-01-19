<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    echo "Unauthorized: No barangay_id in session.";
    exit;
}

$barangayId = $_SESSION['barangay_id'];

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Delete user only if it belongs to the admin's barangay
    $stmt = $conn->prepare("DELETE FROM barangay_registration WHERE id = ? AND barangay_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $barangayId);

        if ($stmt->execute()) {
            echo "User deleted successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing the SQL statement: " . $conn->error;
    }
}
?>
