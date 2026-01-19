<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// For SuperAdmin, use the selected barangay_id, otherwise use the regular barangay_id for Admin/Staff
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id']; // Use selected for SuperAdmin, fallback to regular for Admin/Staff

if (!$barangayId) {
    echo json_encode(['error' => 'No barangay_id in session']);
    exit();
}

try {
    $response = [];

    // Query to count incident types for the selected barangay
    $stmt = $conn->prepare("SELECT incident_type, COUNT(*) as count FROM incident_report WHERE barangay_id = ? GROUP BY incident_type");
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $response[$row['incident_type']] = $row['count'];
        }
    } else {
        $response['error'] = "Query failed: " . $conn->error;
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
