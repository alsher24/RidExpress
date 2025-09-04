<?php
$servername = "localhost";
$username = "root"; // default in XAMPP
$password = "";     // default in XAMPP
$dbname = "ridexpress"; // make sure this is your actual DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
