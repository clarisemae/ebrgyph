<?php
session_start();
$barangayId = $_SESSION['barangay_id'] ?? null;

if (!$barangayId) {
    die("Barangay ID missing in session.");
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ebrgyph";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = $_POST['document_type'];
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $postal_address = $_POST['postal_address'];
    $resident_address = $_POST['resident_address'];
    $remarks = $_POST['remarks'];
    $date_of_birth = $_POST['date_of_birth'];
    $email = $_POST['email'];
    $requested_date = $_POST['requested_date'];
    $id_type = $_POST['id_type'];

    // Handle file uploads
    $photo_1x1_url = "";
    if (isset($_FILES['photo_1x1_url']) && $_FILES['photo_1x1_url']['error'] == 0) {
        $target_dir = "./uploads/";
        $photo_1x1_url = $target_dir . basename($_FILES["photo_1x1_url"]["name"]);
        if (!move_uploaded_file($_FILES["photo_1x1_url"]["tmp_name"], $photo_1x1_url)) {
            echo "Error uploading 1x1 photo.";
            exit;
        }
    }

    $id_photo_url = "";
    if (isset($_FILES['id_photo_url']) && $_FILES['id_photo_url']['error'] == 0) {
        $target_dir = "./uploads/";
        $id_photo_url = $target_dir . basename($_FILES["id_photo_url"]["name"]);
        if (!move_uploaded_file($_FILES["id_photo_url"]["tmp_name"], $id_photo_url)) {
            echo "Error uploading ID photo.";
            exit;
        }
    }

    // Use prepared statement to prevent SQL injection and include barangay_id
    $stmt = $conn->prepare("INSERT INTO certificate_of_comelec_registration
        (document_type, fullname, age, postal_address, resident_address, remarks, date_of_birth, email, requested_date, id_type, photo_1x1_url, id_photo_url, barangay_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param(
        "ssissssssssss",
        $document_type,
        $fullname,
        $age,
        $postal_address,
        $resident_address,
        $remarks,
        $date_of_birth,
        $email,
        $requested_date,
        $id_type,
        $photo_1x1_url,
        $id_photo_url,
        $barangayId
    );

    if ($stmt->execute()) {
        echo "Record added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
