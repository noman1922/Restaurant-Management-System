<?php
session_start();

// Ensure the user is logged in and has the 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$menu_items = [];
$menu_message = '';

// --- Handle Add/Update Menu Item ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = htmlspecialchars($_POST['action']);
    $item_name = htmlspecialchars($_POST['item_name'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $image_url = htmlspecialchars($_POST['image_url'] ?? null); // Can be null if not provided

    if ($action === 'add' || $action === 'update') {
        if (empty($item_name) || $price <= 0) {
            $menu_message = '<p class="message error">Item name and a positive price are required.</p>';
        } else {
            if ($action === 'add') {
                try {
                    $stmt = $conn->prepare("INSERT INTO menu (item_name, description, price, image_url) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssds", $item_name, $description, $price, $image_url);
                    if ($stmt->execute()) {
                        $menu_message = '<p class="message success">Menu item added successfully!</p>';
                    } else {
                        $menu_message = '<p class="message error">Error adding item: ' . htmlspecialchars($stmt->error) . '</p>';
                    }
                    $stmt->close();
                } catch (Exception $e) {
                    error_log("Error adding menu item: " . $e->getMessage());
                    $menu_message = '<p class="message error">An unexpected error occurred while adding the item.</p>';
                }
            } elseif ($action === 'update') {
                $item_id = intval($_POST['item_id'] ?? 0);
                if ($item_id > 0) {
                    try {
                        $stmt = $conn->prepare("UPDATE menu SET item_name = ?, description = ?, price = ?, image_url = ? WHERE id = ?");
                        $stmt->bind_param("ssdssi", $item_name, $description, $price, $image_url, $item_id);
                        if ($stmt->execute()) {
                            $menu_message = '<p class="message success">Menu item updated successfully!</p>';
                        } else {
                            $menu_message = '<p class="message error">Error updating item: ' . htmlspecialchars($stmt->error) . '</p>';
                        }
                        $stmt->close();
                    } catch (Exception $e) {
                        error_log("Error updating menu item: " . $e->getMessage());
                        $menu_message = '<p class="message error">An unexpected error occurred while updating the item.</p>';
                    }
                } else {
                    $menu_message = '<p class="message error">Invalid item ID for update.</p>';
                }
            }
        }
    }
}

// --- Handle Delete Menu Item ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $item_id = intval($_GET['id'] ?? 0);
    if ($item_id > 0) {
        try {
            $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            if ($stmt->execute()) {
                $menu_message = '<p class="message success">Menu item deleted successfully!</p>';
            } else {
                $menu_message = '<p class="message error">Error deleting item: ' . htmlspecialchars($stmt->error) . '</p>';
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error deleting menu item: " . $e->getMessage());
            $menu_message = '<p class="message error">An unexpected error occurred while deleting the item.</p>';
        }
    } else {
        $menu_message = '<p class="message error">Invalid item ID for deletion.</p>';
    }
}

// --- Fetch all menu items for display ---
try {
    $stmt = $conn->prepare("SELECT id, item_name, description, price, image_url FROM menu ORDER BY item_name ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $menu_items[] = $row;
        }
    } else {
        $menu_message .= '<p class="message">No menu items found. Add some!</p>';
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching menu items: " . $e->getMessage());
    $menu_message .= '<p class="message error">Error loading menu items. Please try again later.</p>';
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Menu | RMS Admin</title>
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
        .form-section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fdfdfd;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .submit-btn {
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .submit-btn.update {
            background-color: #3498db;
        }
        .submit-btn:hover {
            background-color: #229a54;
        }
        .submit-btn.update:hover {
            background-color: #2980b9;
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
            vertical-align: top; /* Align content to top for description */
        }
        th {
            background-color: #f2f2f2;
            color: #34495e;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .action-btn {
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            margin-right: 5px;
            transition: background-color 0.3s ease;
        }
        .action-btn:hover {
            background-color: #2980b9;
        }
        .delete-btn {
            background-color: #e74c3c;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
        .message {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: left;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
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
        <h1>Admin Dashboard | Modify Menu</h1>
        <a class="logout-btn" href="logout.php">Logout</a>
    </header>

    <div class="container">
        <h2>Add/Update Menu Item</h2>
        <?= $menu_message; ?>

        <div class="form-section">
            <form action="add_menu.php" method="post">
                <input type="hidden" name="action" id="menu_action" value="add">
                <input type="hidden" name="item_id" id="menu_item_id" value="">

                <div class="form-group">
                    <label for="item_name">Item Name:</label>
                    <input type="text" id="item_name" name="item_name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description (Optional):</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price (৳):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="image_url">Image URL (Optional):</label>
                    <input type="text" id="image_url" name="image_url">
                </div>
                <button type="submit" class="submit-btn" id="menu_submit_btn">Add Item</button>
            </form>
        </div>

        <h2>Current Menu Items</h2>
        <?php if (!empty($menu_items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Price (৳)</th>
                        <th>Image URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id']); ?></td>
                            <td><?= htmlspecialchars($item['item_name']); ?></td>
                            <td><?= htmlspecialchars($item['description'] ?? 'N/A'); ?></td>
                            <td>৳<?= number_format($item['price'], 2); ?></td>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <a href="<?= htmlspecialchars($item['image_url']); ?>" target="_blank">View Image</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="action-btn" onclick="editMenuItem(<?= htmlspecialchars(json_encode($item)); ?>)">Edit</button>
                                <a href="add_menu.php?action=delete&id=<?= htmlspecialchars($item['id']); ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this menu item?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No menu items available.</p>
        <?php endif; ?>
    </div>

    <div class="nav-links">
        <a href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>

    <script>
        function editMenuItem(item) {
            document.getElementById('menu_action').value = 'update';
            document.getElementById('menu_item_id').value = item.id;
            document.getElementById('item_name').value = item.item_name;
            document.getElementById('description').value = item.description || '';
            document.getElementById('price').value = item.price;
            document.getElementById('image_url').value = item.image_url || '';
            document.getElementById('menu_submit_btn').textContent = 'Update Item';
            document.getElementById('menu_submit_btn').classList.add('update');
            window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll to top to show form
        }
    </script>
</body>
</html>
