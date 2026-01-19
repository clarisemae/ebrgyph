<?php
include '../includes/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'] ?? null;
    $full_name = $_POST['full_name'] ?? null;
    $age = $_POST['age'] ?? null; // Include age
    $address = $_POST['address'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $sector = $_POST['sector'] ?? null;
    $citizenship = $_POST['citizenship'] ?? null;

    if (!$resident_id || !$full_name || !$age || !$address || !$gender || !$sector || !$citizenship) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $stmt = $conn_residents->prepare("UPDATE resident SET full_name = ?, age = ?, address = ?, gender = ?, sector = ?, citizenship = ? WHERE resident_id = ?");
    if ($stmt) {
        $stmt->bind_param("sissssi", $full_name, $age, $address, $gender, $sector, $citizenship, $resident_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Resident updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
$conn_residents->close();
?>
