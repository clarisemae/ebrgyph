<?php
session_start();
include '../includes/db_connection.php';

header('Content-Type: application/json');

// Use the same connection variable as in your main app
// Assuming it's $conn from db_connection.php

// Determine barangay_id to use:
// For SuperAdmin, use selected barangay if set, else fallback to assigned barangay
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id'] ?? null;

if (!$barangayId) {
    echo json_encode(["error" => "No barangay_id in session"]);
    exit();
}

// Initialize row number variable
if (!$conn->query("SET @row_number = 0")) {
    echo json_encode(["error" => "Failed to initialize row number: " . $conn->error]);
    exit();
}

// Prepare the SELECT query with row numbering and filtering by barangay_id
$stmt = $conn->prepare("
    SELECT 
        (@row_number := @row_number + 1) AS row_number, 
        id, 
        photo, 
        name, 
        role 
    FROM barangay_officials
    WHERE barangay_id = ?
    ORDER BY name ASC
");

if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $barangayId);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $officials = [];
    while ($row = $result->fetch_assoc()) {
        $officials[] = $row;
    }
    echo json_encode($officials);
} else {
    echo json_encode(["error" => "Failed to execute query: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
