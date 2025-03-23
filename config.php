<<<<<<< HEAD
<?php
// Database configuration
$host = 'localhost'; // Database host
$dbname = 'task_manager'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password

// Create a PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Enable error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Exit if the connection fails
    die("Database connection failed: " . $e->getMessage());
}
=======
<?php
// Database configuration
$host = 'localhost'; // Database host
$dbname = 'task_manager'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password

// Create a PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Enable error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Exit if the connection fails
    die("Database connection failed: " . $e->getMessage());
}
>>>>>>> front-end
?>