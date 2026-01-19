<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['barangay_id'])) {
    echo json_encode(['error' => 'No barangay_id in session']);
    exit();
}

$barangayId = $_SESSION['barangay_id'];

// Initialize the row number variable before the query
if (!$conn->query("SET @row_number = 0")) {
    echo json_encode(['error' => 'Failed to initialize row number']);
    exit();
}

$query = "
    SELECT 
        (@row_number := @row_number + 1) AS row_number,
        announcement_id, 
        title, 
        description, 
        date, 
        image, 
        status, 
        created_at
    FROM announcement
    WHERE barangay_id = ?
    ORDER BY announcement_id ASC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}
$stmt->bind_param("i", $barangayId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    echo json_encode($announcements);
} else {
    echo json_encode(["error" => "No announcements found."]);
}

$stmt->close();
$conn->close();
?>
