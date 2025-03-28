<?php
require_once __DIR__ . '/config.php';
global $con; // Access the global connection from config.php

// Start session at the beginning
session_start();

$error = ''; // Variable to store error messages

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // Fetch user from the database
        $stmt = $con->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password and start session
        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_login'] = time();

            // Redirect based on role
            if ($user['role'] === 'manager') {
                header("Location: manager_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            // Generic error message to prevent username enumeration
            $error = "Invalid username or password!";
            // Log failed login attempt (in production)
            error_log("Failed login attempt for username: $username");
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error = "A system error occurred. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container { 
            max-width: 400px; 
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group input { 
            width: 100%; 
            padding: 10px; 
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error { 
            color: #d9534f;
            background: #fdf7f7;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ebccd1;
            margin-bottom: 20px;
        }
        button {
            width: 100%;
            background: #4285f4;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background: #3367d6;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #4285f4;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" minlength="6">
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
