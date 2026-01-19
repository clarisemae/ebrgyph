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
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'], $data['is_checked'])) {
        $id = $data['id'];
        $is_checked = $data['is_checked'] ? 1 : 0; // Convert boolean to integer (1 = checked, 0 = unchecked)

        // Update `is_checked` column in the database
        $query = "UPDATE certificate_of_indigency SET is_checked = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $is_checked, $id);

        if ($stmt->execute()) {
            if ($is_checked === 1) {
                // If checkbox is checked, send an email notification
                $select_query = "SELECT fullname, email FROM certificate_of_indigency WHERE id = ?";
                $select_stmt = $conn->prepare($select_query);
                $select_stmt->bind_param('i', $id);
                $select_stmt->execute();
                $result = $select_stmt->get_result();

                if ($result->num_rows > 0) {
                    $recipient = $result->fetch_assoc();
                    $email_response = sendEmail([
                        'email' => $recipient['email'],
                        'fullname' => $recipient['fullname'],
                    ]);

                    $select_stmt->close();
                    echo json_encode(['success' => true, 'message' => 'Checkbox updated and email sent', 'email_response' => $email_response]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to fetch recipient details']);
                }
            } else {
                // If checkbox is unchecked, just update the database
                echo json_encode(['success' => true, 'message' => 'Checkbox updated to unchecked']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update checkbox']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
}

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
        $mail->setFrom('BaranggayOfficials@gmail.com', 'Officials');
        $mail->addAddress($options['email'], $options['fullname']); // Recipient's email and name

        // Email content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Certificate Marked as Done';
        $mail->Body    = "<p>Hi {$options['fullname']},</p><p>Your certificate has been marked as done.</p>";
        $mail->AltBody = "Hi {$options['fullname']},\n\nYour certificate has been marked as done.";

        // Send the email
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}
