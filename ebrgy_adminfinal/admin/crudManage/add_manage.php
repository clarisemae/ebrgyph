<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// Check user role from session
$role = $_SESSION['role'] ?? null;

// Get barangay_id based on role
if ($role === 'SuperAdmin') {
    // SuperAdmin must specify barangay_id in POST data
    $barangayId = $_POST['barangay_id'] ?? null;
    if (!$barangayId) {
        echo json_encode([
            "status" => "error",
            "message" => "Barangay ID must be specified by SuperAdmin."
        ]);
        exit();
    }
} else {
    // Admin/Staff get barangay_id from session
    $barangayId = $_SESSION['barangay_id'] ?? null;
    if (!$barangayId) {
        echo json_encode([
            "status" => "error",
            "message" => "Barangay ID not found in session."
        ]);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $adminName = trim($_POST['adminName'] ?? '');
    $adminUsername = trim($_POST['adminUsername'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleToAdd = $_POST['role'] ?? 'Admin';

    // Basic validation
    if ($adminName === '' || $adminUsername === '' || $email === '' || $phone === '' || $password === '' || $roleToAdd === '') {
        echo json_encode([
            "status" => "error",
            "message" => "All fields are required."
        ]);
        exit();
    }

    // Additional email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email address."
        ]);
        exit();
    }

    // Password hash
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute insert statement
    $stmt = $conn->prepare("INSERT INTO admin (adminName, adminUsername, email, phone, password, role, barangay_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to prepare the SQL statement: " . $conn->error
        ]);
        exit();
    }

    $stmt->bind_param("ssssssi", $adminName, $adminUsername, $email, $phone, $hashedPassword, $roleToAdd, $barangayId);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Admin account added successfully."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $stmt->error
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method."
    ]);
}

$conn->close();
?>
