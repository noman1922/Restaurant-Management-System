<?php
session_start();
// Check if the user is logged in and has the 'staff' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php"); // Redirect to login if not authorized
    exit();
}
$username = $_SESSION['username'] ?? 'Staff Member';

// --- Calculate Salary Date and Days Remaining ---
$current_date = new DateTime(); // Get current date and time

// Set the target salary date to the 10th of the current month
$salary_date = new DateTime($current_date->format('Y-m-10'));

// If today is after the 10th, the next salary date is the 10th of the *next* month
if ($current_date > $salary_date) {
    $salary_date->modify('+1 month');
}

// Calculate the difference between now and the salary date
$interval = $current_date->diff($salary_date);
$days_remaining = $interval->days;

$formatted_salary_date = $salary_date->format('F j, Y'); // Format: June 10, 2025

// --- End Calculation ---

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Salary Date | RMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f1f2f6;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 100%;
            text-align: center;
        }
        h2 {
            color: #34495e;
            margin-bottom: 20px;
        }
        p {
            color: #555;
            line-height: 1.6;
        }
        .nav-links {
            margin-top: 30px;
        }
        .nav-links a {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 0 10px;
            transition: background-color 0.3s ease;
        }
        .nav-links a:hover {
            background-color: #2980b9;
        }
        .nav-links a.logout-btn {
            background-color: #e74c3c;
        }
        .nav-links a.logout-btn:hover {
            background-color: #c0392b;
        }
        .salary-info {
            font-size: 1.1em;
            margin-top: 20px;
        }
        .highlight-date {
            color: #27ae60;
            font-weight: bold;
            font-size: 1.2em;
        }
        .highlight-days {
            color: #e67e22;
            font-weight: bold;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>View Salary Date for <?= htmlspecialchars($username); ?></h2>
        <p>This page provides information about your upcoming salary payment.</p>

        <div class="salary-info">
            <p>Your next salary is due on: <span class="highlight-date"><?= htmlspecialchars($formatted_salary_date); ?></span></p>
            <p>Days remaining until salary: <span class="highlight-days"><?= htmlspecialchars($days_remaining); ?> days</span></p>
            <p>Please note: This date is a general projection for the 10th of the month.</p>
        </div>
    </div>
    <div class="nav-links">
        <a href="staff_dashboard.php">Back to Dashboard</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
