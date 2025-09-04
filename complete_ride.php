<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

if (isset($_GET['ride_id'])) {
    $ride_id = $_GET['ride_id'];
    $rider_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE ride_requests 
                          SET status='completed'
                          WHERE id=? AND rider_id=?");
    $stmt->bind_param("ii", $ride_id, $rider_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    exit();
}
?>