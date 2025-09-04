<?php
session_start();
include 'db.php'; // Ensure this connects to your DB correctly

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all necessary POST fields are set
    if (
        isset($_POST['ride_request_id']) &&
        isset($_POST['rider_id']) &&
        isset($_POST['rating']) &&
        isset($_POST['comment'])
    ) {
        $user_id = $_SESSION['user_id'];
        $ride_request_id = intval($_POST['ride_request_id']);
        $rider_id = intval($_POST['rider_id']);
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);

        // Basic input validation
        if ($rating < 1 || $rating > 5 || empty($comment)) {
            die("Invalid input. Please provide a valid rating and comment.");
        }

        // Insert the review into the database
        $stmt = $conn->prepare("INSERT INTO reviews (ride_request_id, user_id, rider_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iiiss", $ride_request_id, $user_id, $rider_id, $rating, $comment);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: completed_bookings.php?success=1");
                exit();
            } else {
                echo "Database error: " . $stmt->error;
                $stmt->close();
            }
        } else {
            echo "Prepare failed: " . $conn->error;
        }

        $conn->close();
    } else {
        echo "Missing required fields.";
    }
} else {
    echo "Invalid request method.";
}
?>
