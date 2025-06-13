<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

switch ($role) {
    case 'admin':
        header("Location: admin_dashboard.php");
        break;
    case 'staff':
        header("Location: staff_dashboard.php");
        break;
    case 'guest':
        header("Location: guest_dashboard.php");
        break;
    default:
        echo "Invalid role!";
        session_destroy();
        break;
}
exit();
?>
