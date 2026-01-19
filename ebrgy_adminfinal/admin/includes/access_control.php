<?php
function require_role(array $allowed_roles) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


    if (!isset($_SESSION['role'])) {
        header("Location: admin_login.php");
        exit();
    }

    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Optionally redirect to "Access Denied" page or homepage
        header("HTTP/1.1 403 Forbidden");
        echo "Access Denied: You do not have permission to access this page.";
        exit();
    }
}
?>
