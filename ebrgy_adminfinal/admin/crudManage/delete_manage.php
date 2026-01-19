<?php
include '../includes/db_connection.php';

if (isset($_POST['delete_manage'])) {
    $admin_id = $_POST['admin_id'];

    // Use prepared statement to delete the admin account
    $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);

    if ($stmt->execute()) {
        echo "Admin account deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
