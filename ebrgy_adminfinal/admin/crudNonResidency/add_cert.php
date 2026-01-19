<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['barangay_id'])) {
        echo "Unauthorized: No barangay_id in session.";
        exit;
    }
    $barangay_id = $_SESSION['barangay_id'];

    $document_type = $_POST['document_type'];
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $status = $_POST['status'];
    $citizen = $_POST['citizen'];
    $postal_address = $_POST['postal_address'];
    $requested_date = $_POST['requested_date'];
    $email = $_POST['email'];
    $non_residency_purpose = $_POST['non_residency_purpose'];
    $other_purpose = $_POST['other_purpose'];
    $id_type = $_POST['id_type'];
    $subject_fullname = $_POST['subject_fullname'];
    $subject_dob = $_POST['subject_dob'];
    $subject_age = $_POST['subject_age'];

    // Handle file upload
    $id_photo_url = "";
    if (isset($_FILES['id_photo_url']) && $_FILES['id_photo_url']['error'] == 0) {
        $target_dir = "./uploads/";
        $id_photo_url = $target_dir . basename($_FILES["id_photo_url"]["name"]);
        if (!move_uploaded_file($_FILES["id_photo_url"]["tmp_name"], $id_photo_url)) {
            echo "Error uploading file.";
            exit;
        }
    }

    // Use prepared statement for security
    $stmt = $conn->prepare("INSERT INTO certificate_of_non_residency 
        (document_type, fullname, age, status, citizen, postal_address, requested_date, email, non_residency_purpose, other_purpose, id_type, subject_fullname, subject_dob, subject_age, id_photo_url, barangay_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("ssissssssssssssis", 
        $document_type, $fullname, $age, $status, $citizen, $postal_address, $requested_date, $email, 
        $non_residency_purpose, $other_purpose, $id_type, $subject_fullname, $subject_dob, $subject_age, $id_photo_url, $barangay_id);

    if ($stmt->execute()) {
        echo "Record added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
