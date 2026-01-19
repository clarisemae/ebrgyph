<?php
session_start();
include '../includes/db_connection.php';

header('Content-Type: application/json');

$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id'];

if (!$barangayId) {
    echo json_encode(['error' => 'No barangay_id in session']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM barangay_certificate WHERE barangay_id = ?");
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    echo json_encode(['barangay_certificate' => $count]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
