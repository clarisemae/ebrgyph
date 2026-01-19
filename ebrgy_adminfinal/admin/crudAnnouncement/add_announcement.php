<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

// Check session barangay_id
if (!isset($_SESSION['barangay_id'])) {
    echo json_encode(["status" => "error", "message" => "No barangay_id in session."]);
    exit;
}

$barangayId = $_SESSION['barangay_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? null;
    $date = $_POST['date'] ?? null;
    $description = $_POST['description'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$title || !$date || !$description || !$status) {
        echo json_encode(["status" => "error", "message" => "All fields are required, including status."]);
        exit;
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        echo json_encode(["status" => "error", "message" => "Invalid status value."]);
        exit;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = __DIR__ . '/uploads/';
        $uploadFile = $uploadDir . $imageName;

        if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            echo json_encode(["status" => "error", "message" => "Failed to create uploads directory."]);
            exit;
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Image is required."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO announcement (title, description, date, image, status, barangay_id) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssssi", $title, $description, $date, $imageName, $status, $barangayId);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Announcement added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to prepare the SQL statement."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

$conn->close();
?>
