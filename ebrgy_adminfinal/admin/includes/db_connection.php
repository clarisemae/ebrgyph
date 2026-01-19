<?php
$servername = "localhost"; // Change if not localhost
$username = "root";        // Database username
$password = "";            // Database password
$dbname = "ebrgyph";         // Database name

// Common database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error, 3, '/var/log/php_errors.log');
    die("Unable to connect to the database. Please try again later.");
}

// Alias connections for clarity (optional)
$conn_residents = $conn;
$conn_documents = $conn;
$conn_announcements = $conn;
?>
