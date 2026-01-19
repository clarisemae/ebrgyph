<?php
header('Content-Type: application/json');
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch POST data
    $id = $_POST['id'] ?? null;
    $complainant = $_POST['complainant'] ?? '';
    $accused = $_POST['accused'] ?? '';
    $incident_type = $_POST['incident_type'] ?? '';
    $other_incident = $_POST['other_incident'] ?? '';
    $incident_address = $_POST['incident_address'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing ID.']);
        exit;
    }

    // Update query
    $sql = "UPDATE incident_report 
            SET complainant = ?, accused = ?, incident_type = ?, other_incident = ?, 
                incident_address = ?, date = ?, time = ?, message = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $complainant, $accused, $incident_type, $other_incident, 
                      $incident_address, $date, $time, $message, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Record updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update record.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
