<?php
session_start(); // Start the session at the very top

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/config.php'; // Fixed include path
global $con; // Access the global connection from config.php

// Fetch tasks for the logged-in user
$user_id = $_SESSION['user_id'];
try {
    $stmt = $con->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error loading tasks. Please try again later.");
}

// Handle task status update
if (isset($_GET['update']) && isset($_GET['status'])) {
    $task_id = filter_input(INPUT_GET, 'update', FILTER_VALIDATE_INT);
    $status = in_array($_GET['status'], ['pending', 'completed']) ? $_GET['status'] : null;

    if ($task_id && $status) {
        try {
            $stmt = $con->prepare("UPDATE tasks SET status = :status WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':id', $task_id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
        }
        header("Location: index.php");
        exit();
    }
}

// Handle task remarks update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_remarks'])) {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $remarks = filter_input(INPUT_POST, 'remarks', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($task_id && $remarks) {
        try {
            $stmt = $con->prepare("UPDATE tasks SET remarks = :remarks WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':id', $task_id, PDO::PARAM_INT);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Remarks update error: " . $e->getMessage());
        }
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .task { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin-bottom: 15px; 
            border-radius: 5px;
            background: #f9f9f9;
        }
        .task h3 { 
            margin: 0 0 10px 0;
            color: #333;
        }
        .task p { 
            margin: 5px 0;
            color: #555;
        }
        .task .status { 
            color: green; 
            font-weight: bold;
        }
        .task .status.completed {
            color: #28a745;
        }
        .task .status.pending {
            color: #dc3545;
        }
        .task .actions a { 
            margin-right: 10px; 
            text-decoration: none; 
            color: #007bff;
            font-weight: bold;
        }
        .task .actions a:hover {
            text-decoration: underline;
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group textarea { 
            width: 100%; 
            padding: 10px; 
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 80px;
        }
        button {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .logout-link {
            float: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Dashboard 
            <span class="logout-link">
                (<a href="logout.php">Logout</a>)
            </span>
        </h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

        <h3>Your Tasks</h3>
        <?php if (empty($tasks)): ?>
            <p>No tasks found.</p>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task">
                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                    <p class="status <?php echo htmlspecialchars($task['status']); ?>">
                        Status: <?php echo htmlspecialchars($task['status']); ?>
                    </p>
                    <p>Remarks: <?php echo htmlspecialchars($task['remarks'] ?? 'No remarks'); ?></p>
                    <div class="actions">
                        <?php if ($task['status'] === 'pending'): ?>
                            <a href="index.php?update=<?php echo (int)$task['id']; ?>&status=completed">
                                Mark as Completed
                            </a>
                        <?php else: ?>
                            <a href="index.php?update=<?php echo (int)$task['id']; ?>&status=pending">
                                Revert to Pending
                            </a>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="remarks-<?php echo (int)$task['id']; ?>">Add Remarks</label>
                            <textarea 
                                id="remarks-<?php echo (int)$task['id']; ?>" 
                                name="remarks" 
                                required
                                placeholder="Enter your remarks here..."></textarea>
                        </div>
                        <input type="hidden" name="task_id" value="<?php echo (int)$task['id']; ?>">
                        <button type="submit" name="add_remarks">Submit Remarks</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
