<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php"); // Redirect if the user is not logged in
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['admin_id'];
    $name = $_POST['adminName'] ?? null;
    $username = $_POST['adminUsername'] ?? null;
    $email = $_POST['email'] ?? null;
    $profilePicture = $_FILES['profile_picture'] ?? null;

    // Password Change Logic
    $newPassword = $_POST['new_password'] ?? null;
    $confirmPassword = $_POST['confirm_password'] ?? null;

    // Initialize updates array and params
    $updates = [];
    $params = [];
    $types = '';

    // Update fields if they are provided
    if ($name) {
        $updates[] = "adminName = ?";
        $params[] = $name;
        $types .= 's';
    }
    if ($username) {
        $updates[] = "adminUsername = ?";
        $params[] = $username;
        $types .= 's';
    }
    if ($email) {
        $updates[] = "email = ?";
        $params[] = $email;
        $types .= 's';
    }

    // Handle profile picture upload
    if ($profilePicture && $profilePicture['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($profilePicture['name'], PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = 'uploads/';
            $fileName = uniqid('profile_', true) . '.' . $fileExtension;
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($profilePicture['tmp_name'], $targetFile)) {
                $updates[] = "profile_picture = ?";
                $params[] = $fileName; // Store only the file name in the database
                $types .= 's';
            } else {
                $_SESSION['update_success'] = "Failed to upload profile picture.";
                header("Location: ../account-settings.php");
                exit;
            }
        } else {
            $_SESSION['update_success'] = "Invalid file format. Only jpg, jpeg, and png are allowed.";
            header("Location: ../account-settings.php");
            exit;
        }
    }

    // Handle password change
    if ($newPassword && $confirmPassword) {
        if ($newPassword === $confirmPassword) {
            // Hash the new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // Add password update to the query
            $updates[] = "password = ?";
            $params[] = $newPasswordHash;
            $types .= 's';
        } else {
            $_SESSION['update_success'] = "New passwords do not match.";
            header("Location: ../account-settings.php");
            exit;
        }
    }

    // Update database if there are changes
    if (!empty($updates)) {
        $updatesSql = implode(", ", $updates);
        $sql = "UPDATE admin SET $updatesSql WHERE id = ?";
        $params[] = $userId;
        $types .= 'i';

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
            $_SESSION['update_success'] = "Account updated successfully!";
        } else {
            $_SESSION['update_success'] = "Failed to update account.";
        }
    } else {
        $_SESSION['update_success'] = "No changes were made.";
    }

    header("Location: ../account-settings.php");
    exit;
}
?>
