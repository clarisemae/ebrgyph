<?php
session_start();
include '../includes/db_connection.php';

header('Content-Type: application/json');

// For SuperAdmin, use selected barangay; else use assigned barangay_id
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id'];

if (!$barangayId) {
    echo json_encode(["error" => "No barangay_id in session"]);
    exit();
}

try {
    // Using ROW_NUMBER() for row count - MySQL 8+
    $stmt = $conn->prepare("
        SELECT 
            ROW_NUMBER() OVER (ORDER BY id) AS row_number,
            id, name, email, date, type, comment, submitted_at
        FROM insights
        WHERE barangay_id = ?
        ORDER BY id
    ");
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $result = $stmt->get_result();

    $insights = [];
    while ($row = $result->fetch_assoc()) {
        $insights[] = $row;
    }

    if (empty($insights)) {
        echo json_encode(["message" => "No insights found."]);
    } else {
        echo json_encode($insights);
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch insights: " . $e->getMessage()]);
}

$conn->close();
?>
