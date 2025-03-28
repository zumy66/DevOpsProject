<?php
session_start(); // Must be at the very top

// Secure session management
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/config.php'; // Fixed include path
global $con; // Use the global connection

// Fetch all users for task creation
try {
    $users = $con->query("SELECT id, username FROM users WHERE role = 'user'")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error (fetch users): " . $e->getMessage());
    $users = [];
}

// Fetch all tasks with user details
try {
    $tasks = $con->query("
        SELECT tasks.*, users.username 
        FROM tasks 
        JOIN users ON tasks.user_id = users.id
        ORDER BY tasks.status, tasks.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error (fetch tasks): " . $e->getMessage());
    $tasks = [];
}

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if ($title && $description && $user_id) {
        try {
            $stmt = $con->prepare("INSERT INTO tasks (title, description, user_id) VALUES (:title, :description, :user_id)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Task creation failed: " . $e->getMessage());
        }
        header("Location: manager_dashboard.php");
        exit();
    }
}

// Handle task status update
if (isset($_GET['update']) && isset($_GET['status'])) {
    $task_id = filter_input(INPUT_GET, 'update', FILTER_VALIDATE_INT);
    $status = in_array($_GET['status'], ['pending', 'completed']) ? $_GET['status'] : null;

    if ($task_id && $status) {
        try {
            $stmt = $con->prepare("UPDATE tasks SET status = :status WHERE id = :id");
            $stmt->bindParam(':id', $task_id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Status update failed: " . $e->getMessage());
        }
        header("Location: manager_dashboard.php");
        exit();
    }
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = filter_input(INPUT_GET, 'delete_user', FILTER_VALIDATE_INT);
    
    if ($user_id) {
        try {
            // First delete user's tasks to maintain referential integrity
            $con->beginTransaction();
            $con->exec("DELETE FROM tasks WHERE user_id = $user_id");
            $stmt = $con->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $con->commit();
        } catch (PDOException $e) {
            $con->rollBack();
            error_log("User deletion failed: " . $e->getMessage());
        }
        header("Location: manager_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
            color: #333;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .logout-link {
            color: #dc3545;
            text-decoration: none;
            font-weight: 500;
        }
        .logout-link:hover {
            text-decoration: underline;
        }
        .section {
            margin-bottom: 40px;
        }
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .task, .user { 
            border: 1px solid #dee2e6; 
            padding: 20px; 
            margin-bottom: 15px; 
            border-radius: 5px;
            background: white;
            transition: transform 0.2s;
        }
        .task:hover, .user:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .task h3, .user h3 { 
            margin: 0 0 10px 0;
            color: #3498db;
        }
        .task p, .user p { 
            margin: 5px 0;
            color: #7f8c8d;
        }
        .task .status { 
            font-weight: bold;
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .status.completed {
            background: #d4edda;
            color: #155724;
        }
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        .actions {
            margin-top: 15px;
        }
        .actions a { 
            margin-right: 15px; 
            text-decoration: none; 
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .actions a.complete {
            color: #28a745;
            border: 1px solid #28a745;
        }
        .actions a.revert {
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        .actions a.delete {
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        .actions a:hover {
            opacity: 0.8;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        .form-group input, 
        .form-group select, 
        .form-group textarea { 
            width: 100%; 
            padding: 10px; 
            box-sizing: border-box;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-family: inherit;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        button {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }
        button:hover {
            background: #2980b9;
        }
        .empty-message {
            color: #6c757d;
            font-style: italic;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Manager Dashboard</h2>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>

        <!-- Create Task Form -->
        <div class="section">
            <h3 class="section-title">Create New Task</h3>
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
                            <option value="<?php echo (int)$user['id']; ?>">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="create_task">Create Task</button>
            </form>
        </div>

        <!-- Task List -->
        <div class="section">
            <h3 class="section-title">All Tasks</h3>
            <?php if (empty($tasks)): ?>
                <p class="empty-message">No tasks found.</p>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task">
                            <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p><?php echo htmlspecialchars($task['description']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status <?php echo htmlspecialchars($task['status']); ?>">
                                    <?php echo htmlspecialchars($task['status']); ?>
                                </span>
                            </p>
                            <p><strong>Assigned to:</strong> <?php echo htmlspecialchars($task['username']); ?></p>
                            <p><strong>Created:</strong> <?php echo date('M j, Y g:i a', strtotime($task['created_at'])); ?></p>
                            <div class="actions">
                                <?php if ($task['status'] === 'pending'): ?>
                                    <a href="manager_dashboard.php?update=<?php echo (int)$task['id']; ?>&status=completed" class="complete">
                                        Mark as Completed
                                    </a>
                                <?php else: ?>
                                    <a href="manager_dashboard.php?update=<?php echo (int)$task['id']; ?>&status=pending" class="revert">
                                        Revert to Pending
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- User Management -->
        <div class="section">
            <h3 class="section-title">User Management</h3>
            <?php if (empty($users)): ?>
                <p class="empty-message">No users found.</p>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($users as $user): ?>
                        <div class="user">
                            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                            <div class="actions">
                                <a href="#" 
                                   onclick="confirmDelete(<?php echo (int)$user['id']; ?>)" 
                                   class="delete">
                                    Delete User
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="modal-content">
            <h3>Confirm User Deletion</h3>
            <p>Are you sure you want to delete this user? All their tasks will also be deleted.</p>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button onclick="document.getElementById('confirmationModal').style.display='none'" 
                        style="background: #6c757d;">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" 
                        style="background: #dc3545;">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(userId) {
            const modal = document.getElementById('confirmationModal');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            modal.style.display = 'flex';
            
            confirmBtn.onclick = function() {
                window.location.href = `manager_dashboard.php?delete_user=${userId}`;
            };
            
            // Close modal when clicking outside
            modal.onclick = function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            };
        }
    </script>
</body>
</html>
