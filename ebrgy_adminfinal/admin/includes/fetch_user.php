<?php
function fetchUserData($conn, $userId) {
    $sql = "SELECT adminName, adminUsername, email, profile_picture FROM admin WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();
        return $userData;
    } else {
        return null; // Return null if the query fails
    }
}
?>