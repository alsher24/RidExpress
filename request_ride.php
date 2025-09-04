<?php
session_start();
include 'db.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate all required fields
    $required_fields = [
        'pickup_location', 'pickup_lat', 'pickup_lng',
        'destination_location', 'destination_lat', 'destination_lng',
        'price'
    ];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die("Error: Missing required field: $field");
        }
    }

    $user_id = $_SESSION['user_id'];
    $pickup_location = $_POST['pickup_location'];
    $pickup_lat = $_POST['pickup_lat'];
    $pickup_lng = $_POST['pickup_lng'];
    $destination_location = $_POST['destination_location'];
    $destination_lat = $_POST['destination_lat'];
    $destination_lng = $_POST['destination_lng'];
    $price = $_POST['price'];

    // Use prepared statements
    $stmt = $conn->prepare("INSERT INTO ride_requests 
                          (user_id, pickup_location, pickup_lat, pickup_lng, 
                           destination_location, destination_lat, destination_lng, 
                           status, price) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
    
    $stmt->bind_param("issssssd", 
                     $user_id, 
                     $pickup_location, 
                     $pickup_lat, 
                     $pickup_lng, 
                     $destination_location, 
                     $destination_lat, 
                     $destination_lng, 
                     $price);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>