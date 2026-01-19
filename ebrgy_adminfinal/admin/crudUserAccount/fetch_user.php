<?php
session_start();
include '../includes/db_connection.php';

header('Content-Type: application/json');

if (!$conn_residents) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}

// Determine which barangay_id to use:
// SuperAdmin uses selected barangay (if any), otherwise fallback to regular barangay_id
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id'];

if (!$barangayId) {
    echo json_encode(["status" => "error", "message" => "No barangay_id found in session."]);
    exit;
}

// Fetch users only from the resolved barangay_id
$query = "SELECT 
            id, 
            full_name, 
            birthdate, 
            gender, 
            civil_status, 
            email, 
            phone, 
            street, 
            barangay, 
            municipality, 
            city, 
            region, 
            emergency_name, 
            emergency_address, 
            emergency_relationship, 
            emergency_phone, 
            created_at 
          FROM barangay_registration
          WHERE barangay_id = ?";

$stmt = $conn_residents->prepare($query);
$stmt->bind_param("i", $barangayId);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to fetch users: " . $conn_residents->error]);
}

$stmt->close();
$conn_residents->close();
?>
