<?php
session_start(); // Make sure session is started

include '../includes/db_connection.php';

header('Content-Type: application/json');

// Ensure user has barangay_id in session
if (!isset($_SESSION['barangay_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized: No barangay assigned"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $announcement_id = $_POST['announcement_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $date = $_POST['date'] ?? null;
    $description = $_POST['description'] ?? null;
    $status = $_POST['status'] ?? null; // Optional if status is included
    $imageName = null;

    $barangayId = $_SESSION['barangay_id'];

    // Validate required fields
    if (!$announcement_id || !$title || !$date || !$description) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Handle file upload (if a new image is uploaded)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = __DIR__ . '/uploads/';
        $uploadFile = $uploadDir . $imageName;

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
            exit;
        }
    }

    // Build SQL Query
    $query = "UPDATE announcement SET title = ?, date = ?, description = ?";
    $params = [$title, $date, $description];
    $types = "sss";

    if ($imageName) {
        $query .= ", image = ?";
        $params[] = $imageName;
        $types .= "s";
    }

    if ($status) {
        $query .= ", status = ?";
        $params[] = $status;
        $types .= "s";
    }

    // Add barangay_id condition to WHERE clause
    $query .= " WHERE announcement_id = ? AND barangay_id = ?";
    $params[] = $announcement_id;
    $params[] = $barangayId;
    $types .= "ii";

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Failed to prepare statement: " . $conn->error]);
        exit;
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Announcement updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No Changes Detected."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update announcement: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

$conn->close();
?>
