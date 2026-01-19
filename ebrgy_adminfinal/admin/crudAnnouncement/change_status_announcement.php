<?php
include '../includes/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $announcement_id = $_POST['announcement_id'] ?? null;
    $new_status = $_POST['new_status'] ?? null;

    if (!$announcement_id || !$new_status) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
        exit;
    }

    // Validate the status
    $allowedStatuses = ['Active', 'Inactive'];
    if (!in_array($new_status, $allowedStatuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status value.']);
        exit;
    }

    // Update the status in the database
    $stmt = $conn->prepare("UPDATE announcement SET status = ? WHERE announcement_id = ?");
    if ($stmt) {
        $stmt->bind_param('si', $new_status, $announcement_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Announcement status updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
