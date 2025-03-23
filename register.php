<?php
include 'config.php'; // Include database connection

$error = ''; // Variable to store error messages

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $role = $_POST['role']; // Get role (user or manager)

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $error = "Username or email already exists!";
    } else {
        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        $stmt->execute();

        // Redirect to login page
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        .form-group input, .form-group select { 
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
        <div class="logo">DevOps Task Manager</div>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Registration Form -->
        <h2>Register</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <button type="submit">Register</button>
        </form>

        <!-- Login Link -->
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>