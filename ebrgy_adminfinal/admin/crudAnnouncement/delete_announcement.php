<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['barangay_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No barangay_id in session']);
    exit();
}

$barangayId = $_SESSION['barangay_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_announcement'])) {
    $announcement_id = $_POST['announcement_id'] ?? null;

    if (!$announcement_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid announcement ID.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM announcement WHERE announcement_id = ? AND barangay_id = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ii", $announcement_id, $barangayId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Announcement deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No announcement found or no permission.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete announcement: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$conn->close();
?>
