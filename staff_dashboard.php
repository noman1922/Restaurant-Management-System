<?php
session_start();
// Check if the user is logged in and has the 'staff' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php"); // Redirect to login if not authorized
    exit();
}

// Include your database connection file
include 'db.php';

$username = $_SESSION['username'] ?? 'Staff Member'; // For future dynamic info

// Fetch pending orders from the database
// Assuming you have an 'orders' table with at least 'id', 'customer_name', 'table_number', 'total_amount', and 'status' columns.
// And 'status' column has a value like 'pending' for pending orders.
$pending_orders = [];
try {
    // Ensure 'total_amount' is selected as 'total_price' from your rms_db.sql indicates
    // If your column is named 'total_amount', keep it. If 'total_price', change it here.
    // Based on rms_db.sql: `total_price`
    $stmt = $conn->prepare("SELECT order_id AS id, customer_name, table_number, total_price AS total_amount FROM orders WHERE status = 'pending' ORDER BY order_date ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pending_orders[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Log the error or display a user-friendly message
    error_log("Error fetching pending orders: " . $e->getMessage());
    // Optionally, display a message to the user
    // $pending_orders_message = "Could not load pending orders at this time. Please try again later.";
}

mysqli_close($conn); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | RMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f1f2f6;
        }
        header {
            background-color: #34495e;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .dashboard-content {
            display: flex;
            flex-wrap: wrap; /* Allows items to wrap to the next line */
            justify-content: center; /* Center items horizontally */
            gap: 20px; /* Space between cards */
            padding: 30px;
            max-width: 1200px; /* Max width for the content area */
            margin: auto; /* Center the content area */
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            padding: 25px;
            transition: 0.3s;
            flex: 1 1 280px; /* Flex-grow, flex-shrink, basis */
            min-width: 280px; /* Minimum width for cards */
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: bold;
            display: block; /* Make the whole card clickable for links */
            padding: 10px 0; /* Add some padding for better click area */
        }
        .logout-btn {
            margin-top: 10px;
            display: inline-block;
            background: #e67e22;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .logout-btn:hover {
            background: #d35400;
        }

        /* Styles for Pending Orders section */
        .pending-orders-card {
            flex: 1 1 600px; /* Make this card wider */
            max-width: 900px; /* Max width for pending orders */
            text-align: left;
            padding: 30px;
        }
        .pending-orders-card h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #34495e;
        }
        .order-item {
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Allow content to wrap on smaller screens */
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .order-details {
            flex: 3; /* Take more space */
            min-width: 250px; /* Ensure details don't get too squished */
        }
        .order-item p {
            margin: 5px 0;
            color: #444;
        }
        .order-item p strong {
            color: #2c3e50;
        }
        .order-complete-btn {
            background-color: #28a745; /* Green color */
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
            margin-left: 20px; /* Space from details */
            flex-shrink: 0; /* Prevent button from shrinking */
        }
        .order-complete-btn:hover {
            background-color: #218838;
        }
        .no-orders-message {
            text-align: center;
            color: #777;
            padding: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, Staff (<?= htmlspecialchars($username); ?>)</h1>
        <a class="logout-btn" href="logout.php">Logout</a>
    </header>

    <div class="dashboard-content">
        <!-- Staff's Salary Date Card -->
        <div class="card">
            <a href="view_salary.php">View Salary Date</a>
        </div>

        <!-- Pending Orders Section -->
        <div class="card pending-orders-card">
            <h2>Pending Orders</h2>
            <div id="pending-orders-list">
                <?php if (!empty($pending_orders)): ?>
                    <?php foreach ($pending_orders as $order): ?>
                        <div class="order-item" id="order-<?= htmlspecialchars($order['id']); ?>">
                            <div class="order-details">
                                <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']); ?></p>
                                <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
                                <p><strong>Table No.:</strong> <?= htmlspecialchars($order['table_number']); ?></p>
                                <p><strong>Total:</strong> ৳<?= number_format($order['total_amount'], 2); ?></p>
                            </div>
                            <button class="order-complete-btn" data-order-id="<?= htmlspecialchars($order['id']); ?>">Order Complete</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-orders-message">No pending orders at the moment.</p>
                <?php endif; ?>
            </div>
            <?php // if (isset($pending_orders_message)) echo '<p class="no-orders-message">' . $pending_orders_message . '</p>'; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderCompleteButtons = document.querySelectorAll('.order-complete-btn');

            orderCompleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;
                    const orderItem = document.getElementById(`order-${orderId}`);

                    // IMPORTANT: Do NOT use alert() or confirm() in production code for Canvas projects.
                    // For now, I'll keep confirm() as you used it, but be aware it might not work as expected
                    // in some iframe environments or might be undesirable for UX.
                    // A custom modal dialog would be the preferred alternative.
                    if (!confirm(`Are you sure you want to mark Order ID ${orderId} as complete?`)) {
                        return; // Stop if user cancels
                    }

                    // Optimistically remove the order from the UI
                    if (orderItem) {
                        orderItem.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        orderItem.style.opacity = '0';
                        orderItem.style.transform = 'translateY(-20px)';
                        setTimeout(() => {
                            orderItem.remove();
                            // Check if all orders are gone and display 'No pending orders' message
                            const remainingOrders = document.querySelectorAll('.order-item');
                            if (remainingOrders.length === 0) {
                                const pendingOrdersList = document.getElementById('pending-orders-list');
                                if (pendingOrdersList) {
                                    pendingOrdersList.innerHTML = '<p class="no-orders-message">No pending orders at the moment.</p>';
                                }
                            }
                        }, 500); // Wait for animation to complete before removing
                    }

                    // Send an AJAX request to mark the order as complete in the database
                    // This relies on your `complete_order.php` file being correctly set up.
                    fetch('complete_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `order_id=${orderId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log(`Order ${orderId} marked as complete successfully.`);
                            // Here you could add a subtle visual confirmation if needed,
                            // beyond the optimistic removal.
                        } else {
                            console.error(`Error marking order ${orderId} complete:`, data.message);
                            // If an error occurs, you might want to revert the UI change
                            // or display an error message to the user (e.g., a custom modal).
                            // alert(`Failed to mark order ${orderId} as complete: ${data.message}`); // Avoid alert in Canvas
                        }
                    })
                    .catch(error => {
                        console.error('Network error:', error);
                        // alert('An unexpected error occurred while processing your request.'); // Avoid alert in Canvas
                    });
                });
            });
        });
    </script>
</body>
</html>
