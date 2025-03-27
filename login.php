<?php
include 'config.php'; // Include database connection

$error = ''; // Variable to store error messages

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password and start session
    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Store user role in session

        // Redirect based on role
        if ($user['role'] === 'manager') {
            header("Location: manager_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
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
            margin: 50px; 
            background-color: #d5e1df; /* Updated background color */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }
        .container { 
            max-width: 400px; 
            width: 100%; 
            padding: 20px; 
            background-color: #fff; /* White background for the form container */
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            text-align: center; 
        }
        .logo { 
            font-size: 24px; 
            font-weight: bold; 
            color: #4CAF50; /* Green color for the logo */
            margin-bottom: 20px; 
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }
        .form-group input { 
            width: 100%; 
            padding: 8px; 
            box-sizing: border-box; 
            background-color: #b5e7a0; /* Updated input field background color */
            border: 1px solid #ccc; 
            border-radius: 4px; 
        }
        .error { 
            color: red; 
            margin-bottom: 15px; 
            text-align: center; 
        }
        button { 
            width: 100%; 
            padding: 10px; 
            background-color: #4CAF50; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
        }
        button:hover { 
            background-color: #45a049; 
        }
        p { 
            text-align: center; 
            margin-top: 15px; 
        }
        a { 
            color: #4CAF50; 
            text-decoration: none; 
        }
        a:hover { 
            text-decoration: underline; 
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <div class="logo">DevOps Task Manager (V2.00) </div>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <!-- Registration Link -->
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
