<?php
$host = 'localhost';  // Your database host, e.g., localhost or IP
$dbname = 'ridexpress';  // Your database name
$username = 'root';  // Your database username
$password = '';  // Your database password (empty for default XAMPP)

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In case of error, output the error message
    die("Connection failed: " . $e->getMessage());
}
?>
