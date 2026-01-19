<?php
header('Content-Type: application/json');
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['official_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? '';
    $photo_url = '';

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing ID.']);
        exit;
    }

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES['photo']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_url = 'uploads/' . $file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload photo.']);
            exit;
        }
    }

    // Update query based on whether photo is uploaded
    if (!empty($photo_url)) {
        $sql = "UPDATE barangay_officials SET name = ?, role = ?, photo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $role, $photo_url, $id);
    } else {
        $sql = "UPDATE barangay_officials SET name = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $role, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Official updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update official.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
