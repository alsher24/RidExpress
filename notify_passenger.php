<?php
session_start();
include 'db.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ride_id = $_POST['ride_id'];

    // Fetch the passenger's user_id
    $sql = "SELECT user_id FROM ride_requests WHERE id=?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();

    if (!$ride) {
        echo json_encode(["status" => "error", "message" => "Ride not found."]);
        exit();
    }

    $user_id = $ride['user_id'];

    // Insert notification into the notifications table
    $notif_sql = "INSERT INTO notifications (user_id, message, is_read) VALUES (?, 'Your ride has been accepted!', 0)";
    $notif_stmt = $conn->prepare($notif_sql);

    if (!$notif_stmt) {
        echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
        exit();
    }

    $notif_stmt->bind_param("i", $user_id);
    if ($notif_stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to notify passenger."]);
    }
}

$conn->close();
?>
