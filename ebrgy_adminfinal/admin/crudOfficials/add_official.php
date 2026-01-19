<?php
session_start();
include '../includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['barangay_id'])) {
    echo json_encode(["status" => "error", "message" => "Barangay ID not found in session."]);
    exit;
}

$barangayId = $_SESSION['barangay_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_official'])) {
    $name = $_POST['name'] ?? null;
    $role = $_POST['role'] ?? null;
    $photo = "";

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        $photo = $target_dir . basename($_FILES["photo"]["name"]);
    
        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $photo)) {
            echo json_encode(["status" => "error", "message" => "Error uploading photo."]);
            exit;
        }
    }

    if (!$name || !$role) {
        echo json_encode(["status" => "error", "message" => "Name and role are required."]);
        exit;
    }

    $stmt = $conn_residents->prepare("INSERT INTO barangay_officials (name, role, photo, barangay_id) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssi", $name, $role, $photo, $barangayId);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Official added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to prepare the SQL statement."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method or missing 'add_official'."]);
}

$conn_residents->close();
?>
