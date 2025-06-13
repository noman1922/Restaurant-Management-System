<?php
session_start();

// Ensure the user is a guest, otherwise redirect to login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guest') {
    header("Location: login.php");
    exit();
}

// Check if form data is submitted
if (empty($_POST)) {
    echo "<p>No order details received. Please go back to the order summary. <a href='view_menu.php'>View Menu</a></p>";
    exit();
}

// Include your database connection file
include 'db.php'; // Make sure this path is correct

// Retrieve submitted order details
$total_amount = floatval($_POST['total_amount'] ?? 0); // Use floatval for numeric conversion
$customer_name = htmlspecialchars($_POST['name'] ?? 'N/A');
$phone_number = htmlspecialchars($_POST['phone'] ?? 'N/A');
$table_number = intval($_POST['table'] ?? 0); // Use intval for table number
$payment_method = htmlspecialchars($_POST['payment'] ?? 'N/A');

// Generate a display receipt ID. This is separate from the database's auto-incrementing order_id.
$receipt_id = 'RMS' . time() . rand(100, 999);
$order_date = date('Y-m-d H:i:s');
$status = 'pending'; // Initial status for new orders

// Prepare the SQL statement for insertion
$stmt = null; // Initialize $stmt to null
$db_insert_successful = false; // Flag to check if insertion was successful

try {
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, phone_number, table_number, total_price, payment_method, order_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Check if prepare was successful
    if ($stmt === false) {
        throw new Exception("MySQLi Prepare failed: " . $conn->error);
    }

    // Bind parameters: s for string, i for integer, d for double/float
    // s (customer_name), s (phone_number), i (table_number), d (total_amount), s (payment_method), s (order_date), s (status)
    $stmt->bind_param("ssidsis", $customer_name, $phone_number, $table_number, $total_amount, $payment_method, $order_date, $status);

    if ($stmt->execute()) {
        $db_insert_successful = true;
        // If you need the auto-incremented ID of the newly inserted order:
        // $last_order_id_from_db = $conn->insert_id;
    } else {
        // If execute fails, capture the specific error
        error_log("Error inserting order: " . $stmt->error);
        echo "<p style='color: red;'>Database error: " . htmlspecialchars($stmt->error) . "</p>"; // Display actual error
        // Note: For production, you'd hide $stmt->error from users and just show a generic message.
        exit();
    }
} catch (Exception $e) {
    error_log("Exception when inserting order: " . $e->getMessage());
    echo "<p style='color: red;'>An unexpected error occurred: " . htmlspecialchars($e->getMessage()) . "</p>"; // Display actual exception
    // Note: For production, you'd hide $e->getMessage() from users and just show a generic message.
    exit();
} finally {
    if ($stmt) {
        $stmt->close();
    }
    // Only close connection here if it was opened in this script and no other operations are pending
    // If db.php always closes it, no need here.
    // For safety, let's close it here after the main operation.
    if ($conn) {
        mysqli_close($conn);
    }
}

// Only proceed with displaying the receipt if the database insertion was successful
if (!$db_insert_successful) {
    // This case should ideally be caught by the exit() calls above,
    // but as a fallback, ensure nothing is displayed if insertion failed.
    echo "<p style='color: red;'>Order processing failed. Please contact support.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt | RMS</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #ecf0f1; }
        .receipt-container { max-width: 600px; margin: auto; background: white; padding: 20px 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 25px; }
        .receipt-details p { margin-bottom: 10px; line-height: 1.6; }
        .receipt-details p strong { color: #34495e; }
        .total-amount { text-align: right; margin-top: 30px; font-size: 1.3em; color: #e74c3c; font-weight: bold; }
        .thank-you { text-align: center; margin-top: 40px; font-size: 1.1em; color: #27ae60; }
        .back-link { display: block; text-align: center; margin-top: 30px; text-decoration: none; color: #3498db; font-weight: bold; }
        .receipt-header, .receipt-footer { text-align: center; margin-bottom: 20px; }
        .receipt-header h1 { margin: 0; color: #2c3e50; }
        .receipt-header p { margin: 5px 0 0; font-size: 0.9em; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Payment Receipt</h1>
            <p>Restaurant Management System</p>
        </div>
        <hr>
        <h2>Order Confirmation</h2>

        <div class="receipt-details">
            <p><strong>Receipt ID:</strong> <?= htmlspecialchars($receipt_id); ?></p>
            <p><strong>Date & Time:</strong> <?= htmlspecialchars($order_date); ?></p>
            <p><strong>Customer Name:</strong> <?= htmlspecialchars($customer_name); ?></p>
            <p><strong>Phone Number:</strong> <?= htmlspecialchars($phone_number); ?></p>
            <p><strong>Table Number:</strong> <?= htmlspecialchars($table_number); ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $payment_method))); ?></p>
        </div>

        <hr>

        <h3>Items Ordered:</h3>
        <div class="order-items-list">
            <?php
            // The item IDs are passed via a hidden input field 'selected_item_ids[]' in the form
            // from order_summary.php to generate_receipt.php.
            // We need to include 'db.php' again here if it was closed in the 'finally' block above,
            // or modify the logic to keep the connection open.
            // For now, let's re-include it temporarily for this section for clarity.
            // In a production app, you'd typically manage the database connection more centrally.

            // Re-include db.php if it was closed, or ensure $conn is still available
            // For robust code, you might pass $conn or check if it's still open.
            // As a quick fix for display, let's just make sure it's included for this part.
            include_once 'db.php'; // Use include_once to prevent re-inclusion if it's still open

            if (isset($_POST['selected_item_ids']) && is_array($_POST['selected_item_ids'])) {
                $selected_item_ids = array_map('intval', $_POST['selected_item_ids']);
                if (!empty($selected_item_ids)) {
                    // Use prepared statement for fetching items too for security
                    $placeholders = implode(',', array_fill(0, count($selected_item_ids), '?'));
                    $items_query_sql = "SELECT item_name, price FROM menu WHERE id IN (" . $placeholders . ")";
                    $stmt_items = null;
                    try {
                        $stmt_items = $conn->prepare($items_query_sql);
                        if ($stmt_items === false) {
                            throw new Exception("Failed to prepare item query: " . $conn->error);
                        }
                        // Dynamically bind parameters based on the number of IDs
                        $types = str_repeat('i', count($selected_item_ids));
                        $stmt_items->bind_param($types, ...$selected_item_ids);
                        $stmt_items->execute();
                        $result_items = $stmt_items->get_result();

                        if ($result_items && $result_items->num_rows > 0) {
                            while ($row_item = $result_items->fetch_assoc()) {
                                echo "<p>" . htmlspecialchars($row_item['item_name']) . " - ৳" . number_format($row_item['price'], 2) . "</p>";
                            }
                        } else {
                            echo "<p>No specific item details available in this receipt.</p>";
                        }
                    } catch (Exception $e) {
                        error_log("Error fetching receipt items: " . $e->getMessage());
                        echo "<p style='color: red;'>Error loading item details: " . htmlspecialchars($e->getMessage()) . "</p>";
                    } finally {
                        if ($stmt_items) {
                            $stmt_items->close();
                        }
                    }
                } else {
                    echo "<p>No specific item details selected for this order.</p>";
                }
            } else {
                echo "<p>No specific item details were passed to this receipt.</p>";
            }

            // Close connection only if it's still open from this re-include
            // if ($conn) { mysqli_close($conn); } // Avoid closing if main script already closed it
            ?>
        </div>

        <div class="total-amount">
            Total Payable: ৳<?= number_format($total_amount, 2); ?>
        </div>

        <div class="thank-you">
            Thank you for your order!
        </div>
        <a href="view_menu.php" class="back-link">Place Another Order</a>
        <a href="logout.php" class="back-link">logout</a>
    </div>
</body>
</html>
