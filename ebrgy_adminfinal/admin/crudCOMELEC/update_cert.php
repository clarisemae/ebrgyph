<?php
header('Content-Type: application/json');
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $fullname = $_POST['fullname'] ?? '';
    $age = $_POST['age'] ?? null;
    $postal_address = $_POST['postal_address'] ?? '';
    $resident_address = $_POST['resident_address'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $email = $_POST['email'] ?? '';
    $requested_date = $_POST['requested_date'] ?? '';
    $id_type = $_POST['id_type'] ?? '';
    $id_photo_url = '';
    $photo_1x1_url = '';

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
            $id_photo_url = 'crudCOMELEC/uploads/' . $file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload ID photo.']);
            exit;
        }
    }

    // Handle 1x1 photo upload
    if (isset($_FILES['photo_1x1_url']) && $_FILES['photo_1x1_url']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "./uploads/";
        $file_name = basename($_FILES['photo_1x1_url']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['photo_1x1_url']['tmp_name'], $target_file)) {
            $photo_1x1_url = 'crudCOMELEC/uploads/' . $file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload 1x1 photo.']);
            exit;
        }
    }

    // Decide which SQL to use based on whether photo URLs are present
    if (!empty($id_photo_url) || !empty($photo_1x1_url)) {
        $sql = "UPDATE certificate_of_comelec_registration 
                SET fullname = ?, age = ?, postal_address = ?, resident_address = ?, 
                    remarks = ?, date_of_birth = ?, email = ?, requested_date = ?, id_type = ?, id_photo_url = ?, photo_1x1_url = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sisssssssssi',
            $fullname,
            $age,
            $postal_address,
            $resident_address,
            $remarks,
            $date_of_birth,
            $email,
            $requested_date,
            $id_type,
            $id_photo_url,
            $photo_1x1_url,
            $id
        );
    } else {
        $sql = "UPDATE certificate_of_comelec_registration 
                SET fullname = ?, age = ?, postal_address = ?, resident_address = ?, 
                    remarks = ?, date_of_birth = ?, email = ?, requested_date = ?, id_type = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sisssssssi',
            $fullname,
            $age,
            $postal_address,
            $resident_address,
            $remarks,
            $date_of_birth,
            $email,
            $requested_date,
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
