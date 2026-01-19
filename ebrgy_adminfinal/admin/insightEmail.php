<?php
// Ensure PHPMailer is installed and included
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load Composer's autoload file

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include database connection
require 'includes/db_connection.php';  // Use require to ensure the file is loaded

header('Content-Type: application/json'); // Set the content type to application/json

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method. Only POST is allowed.");
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        throw new Exception("Invalid input: ID is required.");
    }

    $id = $data['id'];

    // Prepare the SQL statement to prevent SQL injection
    $select_query = "SELECT name, email FROM insights WHERE id = ?";
    $select_stmt = $conn->prepare($select_query);
    if (!$select_stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $select_stmt->bind_param('i', $id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Recipient details not found for ID: $id");
    }

    $recipient = $result->fetch_assoc();
    $email_response = sendEmail([
        'email' => $recipient['email'],
        'name' => $recipient['name']
    ]);

    $select_stmt->close();
    echo json_encode(['success' => true, 'message' => 'Email sent successfully', 'email_response' => $email_response]);
} catch (Exception $e) {
    // Log exception to a file or a system like Sentry
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()]);
}

/**
 * Function to send an email using PHPMailer
 */
function sendEmail($options)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST']; // SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER']; // SMTP username
        $mail->Password = $_ENV['EMAIL_PASS']; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Encryption (SSL)
        $mail->Port = $_ENV['EMAIL_PORT']; // SMTP port

        // Sender and recipient details
        $mail->setFrom($_ENV['EMAIL_USER'], 'Baranggay Officials'); // Dynamically set the sender
        $mail->addAddress($options['email'], $options['name']);
        $mail->addReplyTo('princeeeyron@gmail.com', 'Prince Aaron');

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Reply from Barangay';
        $mail->Body    = "<p>Hi {$options['name']},</p><p>Your inquiry has been replied to. Please check your email for further details.</p>";
        $mail->AltBody = "Hi {$options['name']}, Your inquiry has been replied to. Please check your email for further details.";

        // Send the email
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}
?>
