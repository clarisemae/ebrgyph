<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// Determine which barangay ID to use
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id'] ?? null;

if (!$barangayId) {
    echo json_encode(["error" => "No barangay_id in session"]);
    exit();
}

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

$query = "SELECT 
            (@row_number := @row_number + 1) AS row_number, 
            id, 
            complainant, 
            accused, 
            incident_type, 
            other_incident,
            incident_address, 
            date, 
            time, 
            message,
            incident_photo,
            created_at
          FROM incident_report, (SELECT @row_number := 0) AS t
          WHERE barangay_id = ?
          ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $barangayId);
$stmt->execute();
$result = $stmt->get_result();

$blotters = [];
while ($row = $result->fetch_assoc()) {
    $blotters[] = $row;
}

echo json_encode($blotters);

$stmt->close();
$conn->close();
?>
