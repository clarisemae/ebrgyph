<?php
session_start();
$barangayId = $_SESSION['barangay_id'] ?? null;

if (!$barangayId) {
    echo "No barangay_id in session";
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ebrgyph";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = $_POST['document_type'];
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $status = $_POST['status'];
    $citizen = $_POST['citizen'];
    $address = $_POST['address'];
    $requested_date = $_POST['requested_date'];
    $email = $_POST['email'];
    $indigency_purpose = $_POST['indigency_purpose'];
    $indigency_other_details = $_POST['indigency_other_details'];
    $id_type = $_POST['id_type'];

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

    $stmt = $conn->prepare("INSERT INTO certificate_of_indigency 
        (document_type, fullname, age, status, citizen, address, requested_date, email, indigency_purpose, indigency_other_details, id_type, id_photo_url, barangay_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $stmt->bind_param("ssissssssssss", 
        $document_type, $fullname, $age, $status, $citizen, $address, $requested_date, $email, $indigency_purpose, $indigency_other_details, $id_type, $id_photo_url, $barangayId);

    if ($stmt->execute()) {
        echo "Record added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
