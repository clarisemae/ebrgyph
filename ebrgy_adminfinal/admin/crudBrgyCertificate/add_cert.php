<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    die("Barangay not set in session.");
}

$barangay_id = $_SESSION['barangay_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = $conn->real_escape_string($_POST['document_type']);
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $age = intval($_POST['age']);
    $status = $conn->real_escape_string($_POST['status']);
    $citizen = $conn->real_escape_string($_POST['citizen']);
    $address = $conn->real_escape_string($_POST['address']);
    $requested_date = $conn->real_escape_string($_POST['requested_date']);
    $email = $conn->real_escape_string($_POST['email']);
    $barangay_certificate_purpose = $conn->real_escape_string($_POST['barangay_certificate_purpose']);
    $barangay_other_details = $conn->real_escape_string($_POST['barangay_other_details']);
    $id_type = $conn->real_escape_string($_POST['id_type']);

    $id_photo_url = "";
    if (isset($_FILES['id_photo_url']) && $_FILES['id_photo_url']['error'] == 0) {
        $target_dir = "./uploads/";
        $filename = basename($_FILES["id_photo_url"]["name"]);
        $id_photo_url = $target_dir . $filename;
        if (!move_uploaded_file($_FILES["id_photo_url"]["tmp_name"], $id_photo_url)) {
            echo "Error uploading file.";
            exit;
        }
    }

    $sql = "INSERT INTO barangay_certificate
        (document_type, fullname, age, status, citizen, address, requested_date, email, barangay_certificate_purpose, barangay_other_details, id_type, id_photo_url, barangay_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssisssssssssi",
        $document_type,
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
        $barangay_id
    );

    if ($stmt->execute()) {
        echo "Record added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
