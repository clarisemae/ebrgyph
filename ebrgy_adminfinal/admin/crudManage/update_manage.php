<?php
include '../includes/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $adminName = $_POST['adminName'] ?? null;
    $adminUsername = $_POST['adminUsername'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $role = $_POST['role'] ?? null;

    // Check if all fields are provided
    if (!$id || !$adminName || !$adminUsername || !$email || !$phone || !$role) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Debug input values (optional for testing)
    file_put_contents('php://stderr', print_r([
        'id' => $id,
        'adminName' => $adminName,
        'adminUsername' => $adminUsername,
        'email' => $email,
        'phone' => $phone,
        'role' => $role,
    ], true));

    // Update record in the database
    $stmt = $conn->prepare("UPDATE admin SET adminName = ?, adminUsername = ?, email = ?, phone = ?, role = ? WHERE id = ?");

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssssi", $adminName, $adminUsername, $email, $phone, $role, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Account updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update account: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

$conn->close();
?>
