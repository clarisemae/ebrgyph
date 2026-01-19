<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

$barangayId = $_SESSION['barangay_id'] ?? null;
if (!$barangayId) {
    echo json_encode(['error' => 'Barangay ID missing in session']);
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT 
            (@row_number := @row_number + 1) AS row_number,
            id,
            document_type,
            fullname,
            age,
            status,
            citizen,
            postal_address,
            requested_date,
            email,
            national_id_purpose,
            other_purpose,
            id_type,
            subject_fullname,
            subject_dob,
            subject_age,
            id_photo_url,
            created_at,
            is_checked
        FROM certificate_of_national_id, (SELECT @row_number := 0) AS row_init
        WHERE barangay_id = ?
        ORDER BY id DESC
    ");
    
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $result = $stmt->get_result();

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    echo json_encode($records);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to fetch records: ' . $e->getMessage()]);
}
?>
