<?php
session_start();

// Ensure the user is logged in and has the 'staff' role for authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Include your database connection file
// Make sure 'db.php' exists and has a working database connection setup.
include 'db.php';

// Set header to return JSON response
header('Content-Type: application/json');

// Check if order_id is provided via POST request
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID not provided.']);
    exit();
}

// Sanitize and validate the order ID
$order_id = (int)$_POST['order_id']; // Cast to integer for security and type consistency

// Prepare and execute the SQL update statement
$stmt = null; // Initialize statement variable
try {
    // IMPORTANT: Ensure your 'orders' table has an 'id' column (or 'order_id' as mapped in staff_dashboard.php)
    // and a 'status' column (ENUM or VARCHAR) that accepts 'completed' and 'pending' values.
    // The query updates the status from 'pending' to 'completed' only if it's currently 'pending'.
    // This prevents accidental re-completion or issues if an order was already completed.
    $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE order_id = ? AND status = 'pending'");

    // Check if the prepare statement failed
    if ($stmt === false) {
        error_log("Complete Order DB Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database prepare error.']);
        exit();
    }

    // Bind the integer order_id parameter
    $stmt->bind_param("i", $order_id);

    // Execute the update query
    if ($stmt->execute()) {
        // Check if any rows were actually affected (i.e., if an order was updated)
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Order marked as complete.']);
        } else {
            // No rows affected might mean the order_id didn't exist or its status was not 'pending'
            echo json_encode(['success' => false, 'message' => 'Order not found or already completed.']);
        }
    } else {
        // If execute failed, capture the specific error
        error_log("Complete Order DB Execute failed for Order ID " . $order_id . ": " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
    }
} catch (Exception $e) {
    // Catch any unexpected exceptions during the process
    error_log("Exception in complete_order.php for Order ID " . $order_id . ": " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
} finally {
    // Ensure the statement and connection are closed
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        mysqli_close($conn);
    }
}
?>