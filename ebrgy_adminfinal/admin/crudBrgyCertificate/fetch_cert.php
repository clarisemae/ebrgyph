<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// Determine barangay id to use: selected_barangay_id for SuperAdmin, else barangay_id
$barangay_id = null;

if (isset($_SESSION['role']) && $_SESSION['role'] === 'SuperAdmin') {
    if (!empty($_SESSION['selected_barangay_id'])) {
        $barangay_id = $_SESSION['selected_barangay_id'];
    } else if (!empty($_SESSION['barangay_ids'])) {
        // fallback to first accessible barangay for superadmin
        $barangay_id = $_SESSION['barangay_ids'][0];
    }
} else {
    $barangay_id = $_SESSION['barangay_id'] ?? null;
}

if (!$barangay_id) {
    echo json_encode(['error' => 'No barangay_id found in session or selection.']);
    exit();
}

$query = "
    SELECT 
        (@row_number := @row_number + 1) AS row_number,
        id, 
        document_type, 
        fullname, 
        age, 
        status, 
        citizen, 
        address, 
        requested_date, 
        email, 
        barangay_certificate_purpose, 
        barangay_other_details, 
        id_type, 
        id_photo_url, 
        created_at, 
        is_checked
    FROM barangay_certificate, (SELECT @row_number := 0) AS row_init
    WHERE barangay_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $barangay_id);
$stmt->execute();
$result = $stmt->get_result();

$certificates = [];
while ($row = $result->fetch_assoc()) {
    $certificates[] = $row;
}

echo json_encode($certificates);

$stmt->close();
$conn->close();
?>
