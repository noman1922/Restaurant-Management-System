<?php
session_start();
// Check if the user is logged in and has the 'staff' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php"); // Redirect to login if not authorized
    exit();
}
// You can retrieve the staff username from the session if needed
$username = $_SESSION['username'] ?? 'Staff Member';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Attendance | RMS</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Today's Attendance for <?= htmlspecialchars($username); ?></h2>
        <p>This page will display the attendance records for all staff members for the current day.</p>
        <p>You would integrate with your database here to fetch and display attendance data (e.g., staff ID, name, check-in time, check-out time).</p>
        <!-- Example attendance table placeholder -->
        <h3>Attendance Data (Placeholder)</h3>
        <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Staff Name</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Check-in Time</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Check-out Time</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: left;">John Doe</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: left;">09:00 AM</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: left;">05:00 PM</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: left;">Present</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: left;">Jane Smith</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: left;">09:15 AM</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: left;">-</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: left;">Present (Clocked In)</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="nav-links">
        <a href="staff_dashboard.php">Back to Dashboard</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
