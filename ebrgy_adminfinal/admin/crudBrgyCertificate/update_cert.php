<?php
header('Content-Type: application/json');
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $fullname = $_POST['fullname'] ?? '';
    $age = $_POST['age'] ?? null;
    $status = $_POST['status'] ?? '';
    $citizen = $_POST['citizen'] ?? '';
    $address = $_POST['address'] ?? '';
    $requested_date = $_POST['requested_date'] ?? '';
    $email = $_POST['email'] ?? '';
    $barangay_certificate_purpose = $_POST['barangay_certificate_purpose'] ?? '';
    $barangay_other_details = $_POST['barangay_other_details'] ?? '';
    $id_type = $_POST['id_type'] ?? '';
    $id_photo_url = '';

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
            $id_photo_url = 'crudBrgyCertificate/uploads/'.$file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload ID photo.']);
            exit;
        }
    }

    if (!empty($id_photo_url)) {
        $sql = "UPDATE barangay_certificate 
                SET fullname = ?, age = ?, status = ?, citizen = ?, address = ?, 
                    requested_date = ?, email = ?, barangay_certificate_purpose = ?, 
                    barangay_other_details = ?, id_type = ?, id_photo_url = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sisssssssssi",
            $fullname,
            $age,
            $status,
            $citizen,
            $address,
            $requested_date,
            $email,
            $barangay_certificate_purpose,
            $barangay_other_details,
            $id_type,
            $id_photo_url,
            $id
        );
    } else {
        $sql = "UPDATE barangay_certificate 
                SET fullname = ?, age = ?, status = ?, citizen = ?, address = ?, 
                    requested_date = ?, email = ?, barangay_certificate_purpose = ?, 
                    barangay_other_details = ?, id_type = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sissssssssi",
            $fullname,
            $age,
            $status,
            $citizen,
            $address,
            $requested_date,
            $email,
            $barangay_certificate_purpose,
            $barangay_other_details,
            $id_type,
            $id
        );
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Record updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update record.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>