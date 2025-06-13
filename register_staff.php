<?php
session_start();

// IMPORTANT: Removed the session role check.
// This page is now designed for new staff to register themselves,
// accessible without needing an existing 'staff' session.

$message = ''; // To display success/error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include your database connection here
    include 'db.php';

    $new_staff_username = htmlspecialchars($_POST['new_username'] ?? '');
    $raw_password = $_POST['new_password'] ?? ''; // Keep raw password for hashing
    $new_staff_password_hashed = password_hash($raw_password, PASSWORD_DEFAULT); // Hash the password!
    $new_staff_role = 'staff'; // Hardcoded to 'staff' as per request

    // Basic validation
    if (empty($new_staff_username) || empty($raw_password)) {
        $message = '<p style="color: red;">Username and password cannot be empty.</p>';
    } else {
        try {
            // Check if username already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_stmt->bind_param("s", $new_staff_username);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $message = '<p style="color: red;">Error: Username already exists. Please choose a different username.</p>';
            } else {
                // Insert the new user into your 'users' table
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sss", $new_staff_username, $new_staff_password_hashed, $new_staff_role);

                if ($insert_stmt->execute()) {
                    $message = '<p style="color: green;">New staff account created successfully! You can now <a href="login.php">login here</a>.</p>';
                    // Clear form fields after successful submission
                    $_POST = array(); // Clear form fields
                    // Redirect to login page after a short delay or immediately
                    header("Location: login.php?registration_success=true");
                    exit();
                } else {
                    $message = '<p style="color: red;">Error creating account: ' . htmlspecialchars($insert_stmt->error) . '</p>';
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        } catch (Exception $e) {
            error_log("Error registering staff: " . $e->getMessage());
            $message = '<p class="message error">An unexpected error occurred while creating the account.</p>';
        } finally {
            mysqli_close($conn); // Close connection after operation
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Staff | RMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f1f2f6;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h2 {
            color: #34495e;
            margin-bottom: 20px;
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
        .form-group input[type="password"] {
            width: calc(100% - 22px); /* Account for padding and border */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        .submit-btn {
            background-color: #27ae60;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }
        .submit-btn:hover {
            background-color: #229a54;
        }
        .nav-link-bottom {
            margin-top: 30px;
        }
        .nav-link-bottom a {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .nav-link-bottom a:hover {
            background-color: #2980b9;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Register New Staff</h2>
        <?= $message; // Display messages here ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="new_username">Username:</label>
                <input type="text" id="new_username" name="new_username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <button type="submit" class="submit-btn">Register Staff</button>
        </form>
    </div>
    <div class="nav-link-bottom">
        <p>Already have an account? <a href="login.php">Go to Login Page</a></p>
    </div>
</body>
</html>
