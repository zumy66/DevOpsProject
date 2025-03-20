<?php
session_start(); // Start the session

// Redirect to login if not authenticated as a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Include database connection

// Fetch all users for task creation
$users = $conn->query("SELECT id, username FROM users WHERE role = 'user'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all tasks with user details
$tasks = $conn->query("SELECT tasks.*, users.username FROM tasks JOIN users ON tasks.user_id = users.id")->fetchAll(PDO::FETCH_ASSOC);

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("INSERT INTO tasks (title, description, user_id) VALUES (:title, :description, :user_id)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header("Location: manager_dashboard.php"); // Refresh the page
    exit();
}

// Handle task status update
if (isset($_GET['update'])) {
    $task_id = $_GET['update'];
    $status = $_GET['status'];

    $stmt = $conn->prepare("UPDATE tasks SET status = :status WHERE id = :id");
    $stmt->bindParam(':id', $task_id);
    $stmt->bindParam(':status', $status);
    $stmt->execute();

    header("Location: manager_dashboard.php"); // Refresh the page
    exit();
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();

    header("Location: manager_dashboard.php"); // Refresh the page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .task, .user { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .task h3, .user h3 { margin: 0; }
        .task p, .user p { margin: 5px 0; }
        .task .status { color: green; font-weight: bold; }
        .task .actions a, .user .actions a { margin-right: 10px; text-decoration: none; color: blue; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manager Dashboard</h2>
        <p>Welcome, <?php echo $_SESSION['username']; ?>! (<a href="logout.php">Logout</a>)</p>

        <!-- Create Task Form -->
        <h3>Create Task for User</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Task Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Task Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="user_id">Assign to User</label>
                <select id="user_id" name="user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="create_task">Create Task</button>
        </form>

        <!-- Task List -->
        <h3>All Users' Tasks</h3>
        <?php if (empty($tasks)): ?>
            <p>No tasks found.</p>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task">
                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                    <p class="status">Status: <?php echo $task['status']; ?></p>
                    <p>Created by: <?php echo htmlspecialchars($task['username']); ?></p>
                    <div class="actions">
                        <?php if ($task['status'] === 'pending'): ?>
                            <a href="manager_dashboard.php?update=<?php echo $task['id']; ?>&status=completed">Mark as Completed</a>
                        <?php else: ?>
                            <a href="manager_dashboard.php?update=<?php echo $task['id']; ?>&status=pending">Revert to Pending</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- User Management -->
        <h3>Manage Users</h3>
        <?php if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <div class="user">
                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    <div class="actions">
                        <a href="manager_dashboard.php?delete_user=<?php echo $user['id']; ?>">Delete User</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>