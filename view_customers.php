<?php
session_start();

// Ensure the user is logged in and has the 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$customers = [];
$total_unique_customers = 0;
$message = '';

try {
    // Fetch unique customer names from the 'orders' table
    // Since there's no dedicated 'customers' table, we infer customers from placed orders.
    $stmt = $conn->prepare("SELECT DISTINCT customer_name, phone_number FROM orders WHERE customer_name IS NOT NULL AND customer_name != '' ORDER BY customer_name ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        $total_unique_customers = count($customers); // Count unique customers found
    } else {
        $message = "No customer records found (based on orders).";
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching customer data: " . $e->getMessage());
    $message = "Error loading customer data. Please try again later.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customers | RMS Admin</title>
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
            max-width: 900px;
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
        .customer-count {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 20px;
            color: #34495e;
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
        <h1>Admin Dashboard | View Customers</h1>
        <a class="logout-btn" href="logout.php">Logout</a>
    </header>

    <div class="container">
        <h2>Restaurant Customers</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (!empty($customers)): ?>
            <p class="customer-count">Total Unique Customers: <?= htmlspecialchars($total_unique_customers); ?></p>
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Phone Number</th>
                        <!-- Add more columns if you add a dedicated customers table -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['customer_name']); ?></td>
                            <td><?= htmlspecialchars($customer['phone_number']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No customers to display.</p>
        <?php endif; ?>
    </div>

    <div class="nav-links">
        <a href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>
</body>
</html>
