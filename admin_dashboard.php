<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | RMS</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f8fa;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            width: 90%;
            margin: 30px auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 3px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card a {
            text-decoration: none;
            color: #34495e;
            font-weight: bold;
        }
        .logout-btn {
            margin-top: 10px;
            display: inline-block;
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, Admin</h1>
        <a class="logout-btn" href="logout.php">Logout</a>
    </header>

    <div class="container">
        <div class="card"><a href="view_orders.php">View Orders</a></div>
        <div class="card"><a href="view_staff.php">Manage Staff</a></div>
        <div class="card"><a href="view_customers.php">View Customers</a></div>
        <div class="card"><a href="view_bills.php">View Bills</a></div>
        <div class="card"><a href="attendance.php">Staff Attendance</a></div>
        <div class="card"><a href="add_menu.php">Modify Menu</a></div>
    </div>
</body>
</html>
