<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// Determine barangay id to use: selected_barangay_id for SuperAdmin, else barangay_id
$barangayId = null;

$role = $_SESSION['role'] ?? null;

if ($role === 'SuperAdmin') {
    if (!empty($_SESSION['selected_barangay_id'])) {
        $barangayId = $_SESSION['selected_barangay_id'];
    } elseif (!empty($_SESSION['barangay_ids'])) {
        // fallback to first accessible barangay for superadmin
        $barangayId = $_SESSION['barangay_ids'][0];
    }
} else {
    $barangayId = $_SESSION['barangay_id'] ?? null;
}

if (!$barangayId) {
    echo json_encode(['error' => 'No barangay_id found in session or selection.']);
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
            postal_address, 
            resident_address, 
            remarks, 
            date_of_birth, 
            email, 
            requested_date, 
            id_type, 
            photo_1x1_url, 
            id_photo_url, 
            created_at, 
            is_checked
        FROM certificate_of_comelec_registration, (SELECT @row_number := 0) AS row_init
        WHERE barangay_id = ?
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
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
?>
