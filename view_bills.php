<?php
session_start();

// Ensure the user is logged in and has the 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$completed_orders = [];
$total_revenue = 0;
$message = '';

try {
    // Fetch all completed orders (which we consider as bills)
    // Note: Assuming 'order_id' is the primary key and 'total_price' is the amount
    $stmt = $conn->prepare("SELECT order_id AS id, customer_name, table_number, total_price, payment_method, order_date FROM orders WHERE status = 'completed' ORDER BY order_date DESC");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $completed_orders[] = $row;
            $total_revenue += $row['total_price']; // Sum up total prices
        }
    } else {
        $message = "No completed bills found.";
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching completed orders for bills report: " . $e->getMessage());
    $message = "Error loading bill data. Please try again later.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bills Report | RMS Admin</title>
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
        .total-revenue {
            font-size: 1.3em;
            font-weight: bold;
            color: #27ae60;
            margin-top: 30px;
            text-align: right;
            padding-right: 15px;
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
        <h1>Admin Dashboard | Bills Report</h1>
        <a class="logout-btn" href="logout.php">Logout</a>
    </header>

    <div class="container">
        <h2>All Completed Bills</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (!empty($completed_orders)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Bill ID</th>
                        <th>Customer Name</th>
                        <th>Table No.</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completed_orders as $bill): ?>
                        <tr>
                            <td><?= htmlspecialchars($bill['id']); ?></td>
                            <td><?= htmlspecialchars($bill['customer_name']); ?></td>
                            <td>৳<?= htmlspecialchars($bill['table_number']); ?></td>
                            <td>৳<?= number_format($bill['total_price'], 2); ?></td>
                            <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $bill['payment_method']))); ?></td>
                            <td><?= htmlspecialchars($bill['order_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total-revenue">
                Total Revenue: ৳<?= number_format($total_revenue, 2); ?>
            </div>
        <?php else: ?>
            <p>No completed bills to display.</p>
        <?php endif; ?>
    </div>

    <div class="nav-links">
        <a href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>
</body>
</html>
