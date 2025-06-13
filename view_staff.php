<?php
session_start();

// Ensure the user is logged in and has the 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$staff_members = [];
$message = '';

try {
    // Fetch all staff members from the 'users' table
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE role = 'staff' ORDER BY username ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $staff_members[] = $row;
        }
    } else {
        $message = "No staff members found in the system.";
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching staff members: " . $e->getMessage());
    $message = "Error loading staff data. Please try again later.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff | RMS Admin</title>
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
        .add-staff-btn {
            display: inline-block;
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        .add-staff-btn:hover {
            background-color: #229a54;
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
        <h1>Admin Dashboard | Manage Staff</h1>
        <a class="logout-btn" href="logout.php">Logout</a>
    </header>

    <div class="container">
        <h2>Restaurant Staff Members</h2>
        <a href="register_staff.php" class="add-staff-btn">Add New Staff Account</a>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (!empty($staff_members)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff_members as $staff): ?>
                        <tr>
                            <td><?= htmlspecialchars($staff['id']); ?></td>
                            <td><?= htmlspecialchars($staff['username']); ?></td>
                            <td><?= htmlspecialchars(ucwords($staff['role'])); ?></td>
                            <td>
                                <!-- Placeholder for Edit/Delete functionality -->
                                <a href="edit_staff.php?id=<?= htmlspecialchars($staff['id']); ?>" class="action-btn">Edit</a>
                                <a href="delete_staff.php?id=<?= htmlspecialchars($staff['id']); ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this staff member?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No staff members to display.</p>
        <?php endif; ?>
    </div>

    <div class="nav-links">
        <a href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>
</body>
</html>
