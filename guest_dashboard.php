<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guest') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome Guest | RMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #ffffff url('https://images.unsplash.com/photo-1504674900247-0877df9cc836') no-repeat center center;
            background-size: cover;
        }
        .overlay {
            background-color: rgba(255,255,255,0.95);
            min-height: 100vh;
            padding: 30px;
        }
        .centered-box {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 3px 15px rgba(0,0,0,0.2);
            text-align: center;
        }
        .centered-box h2 {
            margin-bottom: 20px;
        }
        .centered-box a {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .centered-box a:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="overlay">
        <div class="centered-box">
            <h2>Welcome, Guest</h2>
            <p>Explore our delicious menu and place your order!</p>
            <a href="view_menu.php">Go to Menu</a><br><br>
            <a href="logout.php" style="background: #e74c3c;">Logout</a>
        </div>
    </div>
</body>
</html>
