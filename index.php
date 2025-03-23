<<<<<<< HEAD
<?php
session_start(); // Start the session

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Include database connection

// Fetch tasks for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle task status update
if (isset($_GET['update'])) {
    $task_id = $_GET['update'];
    $status = $_GET['status'];

    // Update task status
    $stmt = $conn->prepare("UPDATE tasks SET status = :status WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $task_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header("Location: index.php"); // Refresh the page
    exit();
}

// Handle task remarks update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_remarks'])) {
    $task_id = $_POST['task_id'];
    $remarks = $_POST['remarks'];

    // Update task remarks
    $stmt = $conn->prepare("UPDATE tasks SET remarks = :remarks WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $task_id);
    $stmt->bindParam(':remarks', $remarks);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header("Location: index.php"); // Refresh the page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; backgroud-color: yellow }
        .container { max-width: 800px; margin: 0 auto; }
        .task { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .task h3 { margin: 0; }
        .task p { margin: 5px 0; }
        .task .status { color: green; font-weight: bold; }
        .task .actions a { margin-right: 10px; text-decoration: none; color: blue; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Dashboard</h2>
        <p>Welcome, <?php echo $_SESSION['username']; ?>! (<a href="logout.php">Logout</a>)</p>

        <h3>Your Tasks</h3>
        <?php foreach ($tasks as $task): ?>
            <div class="task">
                <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                <p><?php echo htmlspecialchars($task['description']); ?></p>
                <p class="status">Status: <?php echo $task['status']; ?></p>
                <p>Remarks: <?php echo htmlspecialchars($task['remarks'] ?? 'No remarks'); ?></p>
                <div class="actions">
                    <?php if ($task['status'] === 'pending'): ?>
                        <a href="index.php?update=<?php echo $task['id']; ?>&status=completed">Mark as Completed</a>
                    <?php else: ?>
                        <a href="index.php?update=<?php echo $task['id']; ?>&status=pending">Revert to Pending</a>
                    <?php endif; ?>
                </div>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="remarks">Add Remarks</label>
                        <textarea id="remarks" name="remarks" required></textarea>
                    </div>
                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                    <button type="submit" name="add_remarks">Submit Remarks</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
=======
<?php
session_start(); // Start the session

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Include database connection

// Fetch tasks for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle task status update
if (isset($_GET['update'])) {
    $task_id = $_GET['update'];
    $status = $_GET['status'];

    // Update task status
    $stmt = $conn->prepare("UPDATE tasks SET status = :status WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $task_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header("Location: index.php"); // Refresh the page
    exit();
}

// Handle task remarks update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_remarks'])) {
    $task_id = $_POST['task_id'];
    $remarks = $_POST['remarks'];

    // Update task remarks
    $stmt = $conn->prepare("UPDATE tasks SET remarks = :remarks WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $task_id);
    $stmt->bindParam(':remarks', $remarks);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header("Location: index.php"); // Refresh the page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; backgroud-color: yellow }
        .container { max-width: 800px; margin: 0 auto; }
        .task { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .task h3 { margin: 0; }
        .task p { margin: 5px 0; }
        .task .status { color: green; font-weight: bold; }
        .task .actions a { margin-right: 10px; text-decoration: none; color: blue; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Dashboard</h2>
        <p>Welcome, <?php echo $_SESSION['username']; ?>! (<a href="logout.php">Logout</a>)</p>

        <h3>Your Tasks</h3>
        <?php foreach ($tasks as $task): ?>
            <div class="task">
                <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                <p><?php echo htmlspecialchars($task['description']); ?></p>
                <p class="status">Status: <?php echo $task['status']; ?></p>
                <p>Remarks: <?php echo htmlspecialchars($task['remarks'] ?? 'No remarks'); ?></p>
                <div class="actions">
                    <?php if ($task['status'] === 'pending'): ?>
                        <a href="index.php?update=<?php echo $task['id']; ?>&status=completed">Mark as Completed</a>
                    <?php else: ?>
                        <a href="index.php?update=<?php echo $task['id']; ?>&status=pending">Revert to Pending</a>
                    <?php endif; ?>
                </div>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="remarks">Add Remarks</label>
                        <textarea id="remarks" name="remarks" required></textarea>
                    </div>
                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                    <button type="submit" name="add_remarks">Submit Remarks</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
>>>>>>> front-end
</html>