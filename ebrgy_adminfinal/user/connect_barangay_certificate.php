<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "ebrgy";

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $document_type = $_POST['document_type'];
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $status = $_POST['status'];
    $citizen = $_POST['citizen'];
    $address = $_POST['address'];
    $requested_date = $_POST['requested_date'];
    $email = $_POST['email'];

    // Process checkbox inputs (implode multiple checkbox values)
    $barangay_certificate_purpose = isset($_POST['barangay_certificate_purpose'])
        ? implode(', ', $_POST['barangay_certificate_purpose'])
        : null;

    // Retrieve the 'Others' input
    $barangay_other_details = !empty($_POST['other_purpose']) ? $_POST['other_purpose'] : null;

    // Handle the ID type and photo
    $id_type = $_POST['id-type'];
    $id_photo_url = null;

    // Handle file upload if provided
    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $id_photo_url = $target_dir . basename($_FILES['id_photo']['name']);
        move_uploaded_file($_FILES['id_photo']['tmp_name'], $id_photo_url);
    }

    // SQL query to insert data
    $sql = "INSERT INTO barangay_certificate (
                document_type, 
                fullname, 
                age, 
                status, 
                citizen, 
                address, 
                requested_date, 
                email, 
                barangay_certificate_purpose, 
                barangay_other_details, 
                id_type, 
                id_photo_url
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $sql);

    // Bind parameters to the statement
    mysqli_stmt_bind_param(
        $stmt,
        "ssisssssssss",
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
        $id_photo_url
    );

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        header("Location: requestsubmission.html");
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo "Invalid request.";
}
