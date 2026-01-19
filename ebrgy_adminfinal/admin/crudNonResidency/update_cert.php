<?php
header('Content-Type: application/json');
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $document_type = $_POST['document_type'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $age = $_POST['age'] ?? null;
    $status = $_POST['status'] ?? '';
    $citizen = $_POST['citizen'] ?? '';
    $postal_address = $_POST['postal_address'] ?? '';
    $requested_date = $_POST['requested_date'] ?? '';
    $email = $_POST['email'] ?? '';
    $non_residency_purpose = $_POST['non_residency_purpose'] ?? '';
    $other_purpose = $_POST['other_purpose'] ?? '';
    $id_type = $_POST['id_type'] ?? '';
    $id_photo_url = ''; // Initialize as empty string to check later

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing ID.']);
        exit;
    }

    // Handle ID photo upload
    if (isset($_FILES['id_photo_url']) && $_FILES['id_photo_url']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "./uploads/";
        $file_name = basename($_FILES['id_photo_url']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['id_photo_url']['tmp_name'], $target_file)) {
            $id_photo_url = 'crudNonResidency/uploads/'.$file_name; // Update file path
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload ID photo.']);
            exit;
        }
    }

    // Decide which SQL to use based on whether photo URLs are present
    if (!empty($id_photo_url)) {
        $sql = "UPDATE certificate_of_non_residency 
                SET document_type = ?, fullname = ?, age = ?, status = ?, citizen = ?, 
                    postal_address = ?, requested_date = ?, email = ?, 
                    non_residency_purpose = ?, other_purpose = ?, id_type = ?, id_photo_url = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssisssssssssi',
            $document_type,
            $fullname,
            $age,
            $status,
            $citizen,
            $postal_address,
            $requested_date,
            $email,
            $non_residency_purpose,
            $other_purpose,
            $id_type,
            $id_photo_url,
            $id
        );
    } else {
        $sql = "UPDATE certificate_of_non_residency 
                SET document_type = ?, fullname = ?, age = ?, status = ?, citizen = ?, 
                    postal_address = ?, requested_date = ?, email = ?, 
                    non_residency_purpose = ?, other_purpose = ?, id_type = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssissssssssi',
            $document_type,
            $fullname,
            $age,
            $status,
            $citizen,
            $postal_address,
            $requested_date,
            $email,
            $non_residency_purpose,
            $other_purpose,
            $id_type,
            $id
        );
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Record updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update record.', 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
