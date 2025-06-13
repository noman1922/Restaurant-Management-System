<?php
session_start(); // Start the session if it's not already started

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit(); // Always exit after a header redirect
?>