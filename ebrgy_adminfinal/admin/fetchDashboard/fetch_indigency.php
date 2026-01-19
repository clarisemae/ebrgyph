<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// For SuperAdmin, use the selected barangay_id, otherwise use the regular barangay_id for Admin/Staff
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id']; // Use selected for SuperAdmin, fallback to regular for Admin/Staff

// Ensure barangay_id is available for Admin/Staff or SuperAdmin
if (!$barangayId) {
    echo json_encode(['error' => 'No barangay_id in session']);
    exit();
}

try {
    $response = [];

    // Query to count certificate of indigency records for the selected barangay
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM certificate_of_indigency WHERE barangay_id = ?");
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();

    $response['certificate_of_indigency'] = $count;
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
