<?php
include '../includes/db_connection.php';

header('Content-Type: application/json');

// Log file path
$logFile = 'crudinsights/logfile.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Enable error reporting for debugging (use only in development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents('php://input'), true);
logMessage("Received data: " . print_r($data, true));

if (isset($data['id'], $data['reply_comment'])) {
    $id = $data['id'];
    $reply_comment = $data['reply_comment'];
    logMessage("Processing reply for ID: $id");

    try {
        // Fetch recipient email and name based on the insight ID
        $query = "SELECT email, name FROM insights WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            logMessage("Fetched email: " . $row['email']);
            $email = $row['email'];
            $name = $row['name'];

            // Prepare and send the email
            $to = $email;
            $subject = "Reply to Your Insight";
            $body = "Hello " . $name . ",\n\n" . $reply_comment;
            $headers = "From: no-reply@yourdomain.com";

            if (mail($to, $subject, $body, $headers)) {
                echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
                logMessage("Email sent successfully to: $email");
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send email']);
                logMessage("Failed to send email to: $email");
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No user found for the given ID']);
            logMessage("No user found for ID: $id");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        logMessage("Error: " . $e->getMessage());
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    logMessage("Invalid input");
}

$conn->close();
