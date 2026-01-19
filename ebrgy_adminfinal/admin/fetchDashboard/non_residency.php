<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// For SuperAdmin, use the selected barangay, otherwise use the regular barangay_id
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id']; // SuperAdmin can select barangay, Admin/Staff uses assigned barangay_id

if (!$barangayId) {
    echo json_encode(['error' => 'No barangay_id in session']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM certificate_of_non_residency WHERE barangay_id = ?");
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();

    echo json_encode(['certificate_of_non_residency' => $count]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
