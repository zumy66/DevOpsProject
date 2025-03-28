<?php
// config.php - Database Connection File

// Database Configuration
$db_host = 'db';          // Docker service name
$db_name = 'task_manager';
$db_user = 'root';
$db_pass = 'Jan@20182019';

// Create global database connection
global $con;

try {
    $con = new PDO(
        "mysql:host=$db_host;dbname=$db_name", 
        $db_user, 
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Test connection immediately
    $con->query("SELECT 1");
} catch (PDOException $e) {
    // Log error and exit
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

// Function to get the connection (optional but recommended)
function getDBConnection() {
    global $con;
    return $con;
}
?>
