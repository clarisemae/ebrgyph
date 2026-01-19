<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Assumes Composer's autoload

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

include 'includes/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'], $data['reply_comment'])) {
        $id = $data['id'];
        $reply_comment = $data['reply_comment'];

        $query = "SELECT name, email FROM insights WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $recipient = $result->fetch_assoc();
            $email_response = sendEmail([
                'email' => $recipient['email'],
                'name' => $recipient['name'], // Changed from fullname to name
                'reply_comment' => $reply_comment
            ]);

            echo json_encode(['success' => true, 'message' => 'Email reply sent', 'email_response' => $email_response]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No user found for the given ID']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
}

function sendEmail($options) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $_ENV['EMAIL_PORT'];

        $mail->setFrom('from@yourdomain.com', 'Barangay Management System');
        $mail->addAddress($options['email'], $options['name']); // Changed the addAddress to use name

        $mail->isHTML(true);
        $mail->Subject = 'Reply to Your Insight';
        $mail->Body    = "Dear {$options['name']},<br><br>{$options['reply_comment']}<br><br>Regards,<br>Your Barangay Management Team";
        $mail->AltBody = "Dear {$options['name']},\n\n{$options['reply_comment']}\n\nRegards,\nYour Barangay Management Team";

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: " . $mail->ErrorInfo];
    }
}
?>