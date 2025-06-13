<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guest') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Retrieve selected items
$selected_items = $_POST['items'] ?? [];

if (empty($selected_items)) {
    echo "<p>No items selected. <a href='view_menu.php'>Go Back</a></p>";
    exit();
}

// Fetch items from the database
$items_query = "SELECT * FROM menu WHERE id IN (" . implode(',', array_map('intval', $selected_items)) . ")";
$result = mysqli_query($conn, $items_query);

// Initialize total amount
$total_amount = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Summary | RMS</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #ecf0f1; }
        .order-container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 10px; }
        h2 { text-align: center; }
        .order-items { margin-bottom: 20px; }
        .order-items p { font-weight: bold; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, select { width: 100%; padding: 10px; }
        .submit-btn { background: #2980b9; color: white; padding: 12px; border: none; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="order-container">
        <h2>Order Summary</h2>
        <div class="order-items">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <p><?= htmlspecialchars($row['item_name']); ?> - ৳<?= number_format($row['price'], 2); ?></p>
                <input type="hidden" name="items[]" value="<?= $row['id']; ?>">
                <?php $total_amount += $row['price']; ?>
            <?php } ?>
        </div>

        <!-- Display total payable amount -->
        <p><strong>Total Payable Amount: ৳<?= number_format($total_amount, 2); ?></strong></p>

        <form action="generate_receipt.php" method="post">
            <input type="hidden" name="total_amount" value="<?= $total_amount; ?>">

            <div class="form-group">
                <label for="name">Your Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label for="table">Table Number:</label>
                <input type="text" name="table" required>
            </div>
            <div class="form-group">
                <label for="payment">Payment Method:</label>
                <select name="payment" required>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="mobile_payment">Mobile Payment</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">Pay & Generate Receipt</button>
        </form>
    </div>
</body>
</html>
