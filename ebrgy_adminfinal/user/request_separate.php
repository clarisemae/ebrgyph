<?php
// Connect to the database
$servername = "localhost";
$username = "root";  // Replace with your database username
$password = "";      // Replace with your database password
$dbname = "ebrgyph"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $status = $_POST['status'];
    $citizen = $_POST['citizen'];
    $address = $_POST['address'];
    $requested_date = $_POST['requested_date'];
    $email = $_POST['email'];
    $document_type = $_POST['document'];
    
    // Handle checkboxes for multiple purposes
    $barangay_certificate_purpose = isset($_POST['barangay_certificate_purpose']) ? implode(", ", $_POST['barangay_certificate_purpose']) : null;
    $barangay_other_details = isset($_POST['barangay_other_details']) ? $_POST['barangay_other_details'] : null;
    $indigency_purpose = isset($_POST['indigency_purpose']) ? implode(", ", $_POST['indigency_purpose']) : null;
    $indigency_other_details = isset($_POST['indigency_other_details']) ? $_POST['indigency_other_details'] : null;
    $national_id_certificate_purpose = isset($_POST['national_id_certificate_purpose']) ? implode(", ", $_POST['national_id_certificate_purpose']) : null;
    $national_id_other_details = isset($_POST['national_id_other_details']) ? $_POST['national_id_other_details'] : null;
    
    // For National ID Certificate, collect subject information
    $subject_name = $_POST['subject_name'] ?? null;
    $subject_birthdate = $_POST['subject_birthdate'] ?? null;
    $subject_age = $_POST['subject_age'] ?? null;
    
    // COMELEC remarks
    $remarks = $_POST['remarks'] ?? null;
    
    // Handle file upload (ID photo)
    if (isset($_FILES['id-photo']) && $_FILES['id-photo']['error'] == 0) {
        $id_photo_url = 'uploads/' . $_FILES['id-photo']['name'];
        move_uploaded_file($_FILES['id-photo']['tmp_name'], $id_photo_url);
    } else {
        $id_photo_url = null;
    }

    // Insert data into the database
    $sql = "INSERT INTO document_requests (
        fullname, age, status, citizen, address, requested_date, email, 
        document_type, barangay_certificate_purpose, barangay_other_details, 
        indigency_purpose, indigency_other_details, national_id_certificate_purpose, 
        national_id_other_details, subject_name, subject_birthdate, subject_age, 
        remarks, date_of_birth, id_type, id_photo_url) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sissssssssssssssssss", 
            $fullname, $age, $status, $citizen, $address, $requested_date, $email,
            $document_type, $barangay_certificate_purpose, $barangay_other_details,
            $indigency_purpose, $indigency_other_details, $national_id_certificate_purpose,
            $national_id_other_details, $subject_name, $subject_birthdate, $subject_age,
            $remarks, $date_of_birth, $id_type, $id_photo_url);

        // Execute the statement
        if ($stmt->execute()) {
            header("Location: requestsubmission.html");
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }

    // Close connection
    $conn->close();
}
?>