<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// Check if the user is SuperAdmin and use the selected barangay_id, else use regular barangay_id
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id']; // Use selected barangay_id for SuperAdmin, otherwise fallback to regular barangay_id

// Check if barangay_id exists in the session
if (!$barangayId) {
    echo json_encode(['error' => 'No barangay_id in session']);
    exit();
}

try {
    // Prepare query to select residents with row numbers filtered by barangay
    $query = "
        SELECT 
            ROW_NUMBER() OVER (ORDER BY full_name) AS row_number,
            resident_id,
            full_name,
            age,
            address,
            gender,
            sector,
            citizenship
        FROM resident
        WHERE barangay_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $result = $stmt->get_result();

    $residents = [];
    while ($row = $result->fetch_assoc()) {
        $residents[] = $row;
    }

    echo json_encode($residents);
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch residents: " . $e->getMessage()]);
}

$conn->close();
?>
