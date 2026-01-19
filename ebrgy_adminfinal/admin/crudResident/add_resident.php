<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['barangay_id'])) {
    echo json_encode(["status" => "error", "message" => "Barangay ID not found in session."]);
    exit;
}

$barangayId = $_SESSION['barangay_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resident'])) {
    $full_name = $_POST['full_name'] ?? null;
    $age = $_POST['age'] ?? null;
    $address = $_POST['address'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $sector = $_POST['sector'] ?? null;
    $citizenship = $_POST['citizenship'] ?? null;

    if (!$full_name || !$age || !$address || !$gender || !$sector || !$citizenship) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    $stmt = $conn_residents->prepare("INSERT INTO resident (full_name, age, address, gender, sector, citizenship, barangay_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sissssi", $full_name, $age, $address, $gender, $sector, $citizenship, $barangayId);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Resident added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to prepare the SQL statement."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$conn_residents->close();
?>