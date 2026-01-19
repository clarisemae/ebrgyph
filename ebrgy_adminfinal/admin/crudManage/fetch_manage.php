<?php
session_start();

include '../includes/db_connection.php';

header('Content-Type: application/json');

// Enable error reporting for debugging (remove on production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Check user role and determine which barangay to filter by
if (!isset($_SESSION['role'])) {
    echo json_encode(["error" => "No role found in session"]);
    exit;
}

$role = $_SESSION['role'];

// For SuperAdmin, use selected barangay if set, else fallback to first accessible barangay
if ($role === 'SuperAdmin') {
    $barangayIds = $_SESSION['barangay_ids'] ?? [];
    if (empty($barangayIds)) {
        echo json_encode(["error" => "No barangays assigned to SuperAdmin"]);
        exit;
    }
    $barangayId = $_SESSION['selected_barangay_id'] ?? $barangayIds[0];
} else {
    // For Admin/Staff, use their assigned barangay_id
    if (!isset($_SESSION['barangay_id'])) {
        echo json_encode(["error" => "No barangay_id in session"]);
        exit;
    }
    $barangayId = $_SESSION['barangay_id'];
}

// Initialize row_number variable before SELECT
if (!$conn->query("SET @row_number = 0")) {
    echo json_encode(["error" => "Failed to initialize row_number: " . $conn->error]);
    exit;
}

// Prepare SQL query with barangay filter
$sql = "
    SELECT 
        (@row_number := @row_number + 1) AS row_number,
        id, 
        adminName, 
        adminUsername, 
        email, 
        phone, 
        role, 
        barangay_id,
        created, 
        updated
    FROM admin
    WHERE barangay_id = ?
    ORDER BY id DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $barangayId);

if (!$stmt->execute()) {
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();

$manageAccounts = [];
while ($row = $result->fetch_assoc()) {
    $manageAccounts[] = $row;
}

echo json_encode($manageAccounts);

$stmt->close();
$conn->close();
