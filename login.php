<?php
session_start();
include 'db.php';

$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? ''; // Get the selected role

    if ($role === 'guest') {
        $_SESSION['role'] = 'guest';
        header("Location: guest_dashboard.php"); // Corrected path (removed /rms/)
        exit();
    }

    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password_input = $_POST['password'] ?? ''; // User's input password

    if (empty($username) || empty($password_input)) {
        $error = "Please enter both username and password.";
    } else {
        $query = "SELECT id, username, password, role FROM users WHERE username = ? AND role = ?";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            $error = "Database error: Failed to prepare statement.";
            error_log("Login prepare failed: " . $conn->error);
        } else {
            $stmt->bind_param("ss", $username, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $hashed_password_from_db = $user['password']; // Get the hashed password from the database

                // Verify the user's input password against the hashed password from the database
                if (password_verify($password_input, $hashed_password_from_db)) {
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: /rms/admin_dashboard.php"); // Redirect to admin dashboard
                    } elseif ($user['role'] === 'staff') {
                        header("Location: /rms/staff_dashboard.php"); // Redirect to staff dashboard
                    } else {
                         // Fallback for unexpected roles, though handled by query
                        header("Location: login.php");
                    }
                    exit();
                } else {
                    $error = "Invalid login credentials!"; // Passwords do not match
                }
            } else {
                $error = "Invalid login credentials!"; // User not found or role mismatch
            }
            $stmt->close();
        }
    }
    mysqli_close($conn); // Close connection if opened
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Restaurant System</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('https://images.unsplash.com/photo-1565299624946-b28f40a0ae38') no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            width: 380px;
            margin: 100px auto;
            background: rgba(255, 255, 255, 0.94);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0,0,0,0.3);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #34495e;
        }
        select, input[type="text"], input[type="password"], button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #bbb;
            font-size: 14px;
            box-sizing: border-box; /* Include padding in width */
        }
        button {
            background-color: #2ecc71;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #27ae60;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .create-link {
            text-align: center;
            margin-top: 15px;
        }
        .create-link a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }
        .create-link a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initial toggle when the page loads
            toggleFields();

            const roleSelect = document.getElementById("role");
            if (roleSelect) {
                roleSelect.addEventListener("change", toggleFields);
            }

            // Check for registration success message in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('registration_success') === 'true') {
                const messageDiv = document.createElement('p');
                messageDiv.className = 'error'; // Reusing 'error' class for styling, but it's a success message
                messageDiv.style.color = 'green';
                messageDiv.textContent = 'Registration successful! Please log in.';
                const loginContainer = document.querySelector('.login-container');
                if (loginContainer) {
                    loginContainer.insertBefore(messageDiv, loginContainer.querySelector('form'));
                }
            }
        });

        function toggleFields() {
            const role = document.getElementById("role").value;
            const loginFields = document.getElementById("loginFields");
            const createAccount = document.getElementById("createAccount");

            if (role === "guest") {
                loginFields.style.display = "none";
                createAccount.style.display = "none";
            } else {
                loginFields.style.display = "block";
                // Show "Create New Staff Account" only if 'staff' is selected
                createAccount.style.display = (role === "staff") ? "block" : "none";
            }
            // Clear username/password fields when toggling to prevent sending old data to server inadvertently
            document.querySelector('input[name="username"]').value = '';
            document.querySelector('input[name="password"]').value = '';
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h2>Restaurant Management System</h2>
        <center>
            <p>LOGIN</p>
        </center>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="">
            <label for="role">Login As:</label>
            <select name="role" id="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="guest">Guest (Customer)</option>
            </select>

            <div id="loginFields">
                <input type="text" name="username" placeholder="Username">
                <input type="password" name="password" placeholder="Password">
            </div>

            <button type="submit" name="login">Login</button>

            <div id="createAccount" class="create-link" style="display:none;">
                <a href="register_staff.php">Create New Staff Account</a>
            </div>
        </form>
    </div>
</body>
</html>
