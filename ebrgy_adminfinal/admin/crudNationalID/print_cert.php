<?php
header('Content-Type: application/json');
include '../includes/db_connection.php';

// Enable error logging for debugging
ini_set('log_errors', 1);
ini_set('error_log', 'php-error.log');
error_reporting(E_ALL);

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
    $national_id_purpose = $_POST['national_id_purpose'] ?? '';
    $other_purpose = $_POST['other_purpose'] ?? '';
    $id_type = $_POST['id_type'] ?? '';
    $id_photo_url = null;
    $is_subject_same = $_POST['is_subject_same'] ?? 0;
    $subject_fullname = $_POST['subject_fullname'] ?? null;
    $subject_dob = $_POST['subject_dob'] ?? null;
    $subject_age = $_POST['subject_age'] ?? null;
    $is_checked = $_POST['is_checked'] ?? 0;

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing ID.']);
        exit;
    }

    // Handle ID photo upload
    if (isset($_FILES['id_photo_url']) && $_FILES['id_photo_url']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/Id_photo/";
        $file_name = basename($_FILES['id_photo_url']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['id_photo_url']['tmp_name'], $target_file)) {
            $id_photo_url = $file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload ID photo.']);
            exit;
        }
    }

    // SQL query
    $sql = "UPDATE certificate_of_national_id 
            SET document_type = ?, fullname = ?, age = ?, status = ?, citizen = ?, 
                postal_address = ?, requested_date = ?, email = ?, 
                national_id_purpose = ?, other_purpose = ?, id_type = ?, 
                id_photo_url = ?, is_subject_same = ?, subject_fullname = ?, 
                subject_dob = ?, subject_age = ?, is_checked = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement.']);
        exit;
    }

    // Bind parameters
    $stmt->bind_param(
        'ssissssssssisssii',
        $document_type,
        $fullname,
        $age,
        $status,
        $citizen,
        $postal_address,
        $requested_date,
        $email,
        $national_id_purpose,
        $other_purpose,
        $id_type,
        $id_photo_url,
        $is_subject_same,
        $subject_fullname,
        $subject_dob,
        $subject_age,
        $is_checked,
        $id
    );

    // Debugging: Log parameters
    error_log("Parameters: " . print_r([$document_type, $fullname, $age, $status, $citizen, $postal_address, $requested_date, $email, $national_id_purpose, $other_purpose, $id_type, $id_photo_url, $is_subject_same, $subject_fullname, $subject_dob, $subject_age, $is_checked, $id], true));

    // Execute and handle response
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