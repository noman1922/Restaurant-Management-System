<?php
session_start();

// Ensure the user is logged in and has the 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$staff_for_attendance = [];
$attendance_message = '';
$today = date('Y-m-d'); // Current date for attendance tracking

// Handle form submission for marking attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $user_id = intval($_POST['user_id']);
    $status = htmlspecialchars($_POST['status']); // 'Present' or 'Absent'

    try {
        // First, check if an attendance record already exists for today for this user
        $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND attendance_date = ?");
        $check_stmt->bind_param("is", $user_id, $today);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing record
            $update_stmt = $conn->prepare("UPDATE attendance SET status = ?, check_in_time = ?, check_out_time = ? WHERE user_id = ? AND attendance_date = ?");
            // Set check-in/out based on status (simple logic for now)
            $check_in = ($status === 'Present') ? date('H:i:s') : null;
            $check_out = null; // No checkout on initial mark, but can be updated later
            $update_stmt->bind_param("sssis", $status, $check_in, $check_out, $user_id, $today);
            if ($update_stmt->execute()) {
                $attendance_message = '<p class="message success">Attendance updated successfully!</p>';
            } else {
                $attendance_message = '<p class="message error">Error updating attendance: ' . htmlspecialchars($update_stmt->error) . '</p>';
            }
            $update_stmt->close();
        } else {
            // Insert new record
            $insert_stmt = $conn->prepare("INSERT INTO attendance (user_id, attendance_date, status, check_in_time) VALUES (?, ?, ?, ?)");
            $check_in = ($status === 'Present') ? date('H:i:s') : null;
            $insert_stmt->bind_param("isss", $user_id, $today, $status, $check_in);
            if ($insert_stmt->execute()) {
                $attendance_message = '<p class="message success">Attendance marked successfully!</p>';
            } else {
                $attendance_message = '<p class="message error">Error marking attendance: ' . htmlspecialchars($insert_stmt->error) . '</p>';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } catch (Exception $e) {
        error_log("Attendance marking error: " . $e->getMessage());
        $attendance_message = '<p class="message error">An error occurred while marking attendance.</p>';
    }
}

// Fetch all staff members (excluding admins) and their attendance status for today
try {
    $stmt = $conn->prepare("
        SELECT
            u.id AS user_id,
            u.username,
            a.status AS attendance_status,
            a.check_in_time,
            a.check_out_time
        FROM
            users u
        LEFT JOIN
            attendance a ON u.id = a.user_id AND a.attendance_date = ?
        WHERE
            u.role = 'staff'
        ORDER BY
            u.username ASC
    ");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $staff_for_attendance[] = $row;
        }
    } else {
        $attendance_message = "No staff members to display attendance for.";
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching staff attendance: " . $e->getMessage());
    $attendance_message = "Error loading staff attendance data. Please try again later.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Attendance | RMS Admin</title>
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
        .attendance-date {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 20px;
            color: #555;
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
        .attendance-form select, .attendance-form button {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 0.9em;
            cursor: pointer;
        }
        .attendance-form button {
            background-color: #3498db;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }
        .attendance-form button:hover {
            background-color: #2980b9;
        }
        .message {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
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
        <h1>Admin Dashboard | Staff Attendance</h1>
        <a class="logout-btn" href="logout.php">Logout</a>
    </header>

    <div class="container">
        <h2>Today's Staff Attendance</h2>
        <p class="attendance-date">Date: <?= htmlspecialchars(date('F j, Y')); ?></p>
        <?= $attendance_message; ?>

        <?php if (!empty($staff_for_attendance)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Current Status</th>
                        <th>Check-in Time</th>
                        <th>Check-out Time</th>
                        <th>Mark Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff_for_attendance as $staff): ?>
                        <tr>
                            <td><?= htmlspecialchars($staff['username']); ?></td>
                            <td>
                                <span style="font-weight: bold; color: <?= ($staff['attendance_status'] == 'Present') ? '#28a745' : (($staff['attendance_status'] == 'Absent') ? '#dc3545' : '#6c757d'); ?>">
                                    <?= htmlspecialchars($staff['attendance_status'] ?? 'Not Marked'); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($staff['check_in_time'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($staff['check_out_time'] ?? 'N/A'); ?></td>
                            <td>
                                <form action="" method="post" class="attendance-form">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($staff['user_id']); ?>">
                                    <select name="status">
                                        <option value="Present" <?= ($staff['attendance_status'] == 'Present') ? 'selected' : ''; ?>>Present</option>
                                        <option value="Absent" <?= ($staff['attendance_status'] == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                    </select>
                                    <button type="submit" name="mark_attendance">Mark</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No staff members found.</p>
        <?php endif; ?>
    </div>

    <div class="nav-links">
        <a href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>
</body>
</html>
