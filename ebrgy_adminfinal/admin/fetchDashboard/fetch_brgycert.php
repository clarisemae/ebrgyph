<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// For SuperAdmin, use the selected barangay, otherwise use the regular barangay_id
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id']; // Use selected for SuperAdmin, otherwise fallback to regular barangay_id

if (!$barangayId) {
    echo json_encode(['error' => 'No barangay_id in session']);
    exit();
}

try {
    $response = [];

    // Query to fetch barangay certificate count for the selected barangay
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM barangay_certificate WHERE barangay_id = ?");
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $response['barangay_certificate'] = $count;

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
