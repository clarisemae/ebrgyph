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
    $national_id_purpose = $_POST['national_id_purpose'] ?? '';
    $other_purpose = $_POST['other_purpose'] ?? '';
    $id_type = $_POST['id_type'] ?? '';
    $id_photo_url = '';
    $subject_fullname = $_POST['subject_fullname'] ?? '';
    $subject_dob = $_POST['subject_dob'] ?? '';
    $subject_age = $_POST['subject_age'] ?? null;
    $is_checked = $_POST['is_checked'] ?? 0;

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
            $id_photo_url = 'crudNationalID/uploads/'.$file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload ID photo.']);
            exit;
        }
    }

    $fieldsToUpdate = "document_type = ?, fullname = ?, age = ?, status = ?, citizen = ?, 
                       postal_address = ?, requested_date = ?, email = ?, 
                       national_id_purpose = ?, other_purpose = ?, id_type = ?, 
                       subject_fullname = ?, subject_dob = ?, subject_age = ?, is_checked = ?";
    $params = [
        $document_type, $fullname, $age, $status, $citizen,
        $postal_address, $requested_date, $email, $national_id_purpose,
        $other_purpose, $id_type, $subject_fullname, $subject_dob,
        $subject_age, $is_checked, $id
    ];
    if (!empty($id_photo_url)) {
        $fieldsToUpdate .= ", id_photo_url = ?";
        $params[] = $id_photo_url;
    }

    $sql = "UPDATE certificate_of_national_id SET $fieldsToUpdate WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Failed to prepare SQL statement: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement.', 'error' => $conn->error]);
        exit;
    }

    // Adding types for bind_param based on parameters
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);

    error_log("Executing update with values: " . json_encode($params));

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Record updated successfully.']);
    } else {
        error_log("Failed to execute update: " . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update record.', 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
