<?php
$host = 'localhost';
$user = 'root';           // Default XAMPP username
$pass = '';               // Default XAMPP password (empty)
$db   = 'rms_db';         // Database name

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
