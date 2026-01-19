<?php
session_start();
header('Content-Type: application/json');
include '../includes/db_connection.php';

if (!isset($_SESSION['barangay_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No barangay_id in session']);
    exit();
}

$barangayId = $_SESSION['barangay_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complainant = $_POST['complainant'] ?? '';
    $accused = $_POST['accused'] ?? '';
    $incident_type = $_POST['incident_type'] ?? '';
    $other_incident = $_POST['other_incident'] ?? '';
    $incident_address = $_POST['incident_address'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($complainant) || empty($accused) || empty($incident_type) || empty($incident_address) || empty($date) || empty($time) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
        exit;
    }

    $sql = "INSERT INTO incident_report (complainant, accused, incident_type, other_incident, incident_address, date, time, message, barangay_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssssssi", $complainant, $accused, $incident_type, $other_incident, $incident_address, $date, $time, $message, $barangayId);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'New record added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add record.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
