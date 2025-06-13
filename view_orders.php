<?php
session_start();

// Ensure the user is logged in and has the 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not authorized
    exit();
}

// Include your database connection file
include 'db.php';

$username = $_SESSION['username'] ?? 'Admin User'; // For dynamic display

$all_orders = [];
$message = '';

try {
    // Fetch all orders from the database, ordered by date descending
    // Note: Assuming 'order_id' is the primary key and 'total_price' is the amount
    $stmt = $conn->prepare("SELECT order_id AS id, customer_name, table_number, total_price, payment_method, order_date, status FROM orders ORDER BY order_date DESC");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $all_orders[] = $row;
        }
    } else {
        $message = "No orders found in the system.";
    }
    $stmt->close();
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error fetching all orders for admin report: " . $e->getMessage());
    $message = "Error loading order data. Please try again later.";
}

mysqli_close($conn); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Orders Report | RMS Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f1f2f6;
            padding: 20px;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 1200px;
            width: 100%;
            margin: auto;
            text-align: center;
        }
        h2 {
            color: #34495e;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #34495e;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-pending {
            color: #e67e22; /* Orange for pending */
            font-weight: bold;
        }
        .status-completed {
            color: #28a745; /* Green for completed */
            font-weight: bold;
        }
        .message {
            padding: 15px;
            background-color: #e7eff6;
            border: 1px solid #c9dff0;
            color: #336699;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .nav-links {
            margin-top: 30px;
            text-align: center;
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
    <header>
        <h1>Admin Dashboard | Total Orders Report</h1>
        <a class="logout-btn" href="logout.php">Logout</a>
    </header>

    <div class="container">
        <h2>All Restaurant Orders</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (!empty($all_orders)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Table No.</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Order Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']); ?></td>
                            <td><?= htmlspecialchars($order['customer_name']); ?></td>
                            <td>৳<?= htmlspecialchars($order['table_number']); ?></td>
                            <td>৳<?= number_format($order['total_price'], 2); ?></td>
                            <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method']))); ?></td>
                            <td><?= htmlspecialchars($order['order_date']); ?></td>
                            <td class="status-<?= htmlspecialchars($order['status']); ?>">
                                <?= htmlspecialchars(ucwords($order['status'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders to display.</p>
        <?php endif; ?>
    </div>

    <div class="nav-links">
        <a href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>
</body>
</html>
