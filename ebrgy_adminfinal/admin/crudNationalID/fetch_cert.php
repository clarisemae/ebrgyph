<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['barangay_id'])) {
    echo json_encode(['error' => 'No barangay_id in session']);
    exit();
}

$barangay_id = $_SESSION['barangay_id'];

try {
    $stmt = $conn->prepare("SELECT 
            id, document_type, fullname, age, status, citizen, postal_address, requested_date, email, national_id_purpose,
            other_purpose, id_type, subject_fullname, subject_dob, subject_age, id_photo_url, created_at, is_checked
        FROM certificate_of_national_id
        WHERE barangay_id = ?
        ORDER BY id ASC");
    
    $stmt->bind_param("i", $barangay_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    echo json_encode($records);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
