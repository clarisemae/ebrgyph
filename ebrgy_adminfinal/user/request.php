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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $fullname = $_POST['fullname'];
    $date = $_POST['date'];
    $purpose = $_POST['purpose'];
    $document = $_POST['document'];
    $email = $_POST['email'];
    $id_type = $_POST['id-type'];

    // Handle file upload
    if (isset($_FILES['id-photo']) && $_FILES['id-photo']['error'] == 0) {
        $id_photo = 'uploads/' . basename($_FILES['id-photo']['name']);
        
        // Create uploads directory if not exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Move the uploaded file to the "uploads" folder
        move_uploaded_file($_FILES['id-photo']['tmp_name'], $id_photo);
    }

    // Insert into the database
    $sql = "INSERT INTO document_requests (fullname, date, purpose, document, email, id_type, id_photo) 
            VALUES ('$fullname', '$date', '$purpose', '$document', '$email', '$id_type', '$id_photo')";

    if ($conn->query($sql) === TRUE) {
        header("Location: requestsubmission.html");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
