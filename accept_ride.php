<?php
session_start();
include 'db.php';

// Fix missing parenthesis and add proper error checking
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ride_id = $_POST['ride_id'];
    $rider_id = $_SESSION['user_id'];

    try {
        // Verify ride exists
        $stmt = $conn->prepare("SELECT id FROM ride_requests WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $ride_id);
        $stmt->execute();
        
        if (!$stmt->get_result()->num_rows) {
            throw new Exception('Ride no longer available');
        }

        // Update ride status
        $update = $conn->prepare("UPDATE ride_requests 
                                SET status = 'accepted', rider_id = ?
                                WHERE id = ?");
        $update->bind_param("ii", $rider_id, $ride_id);
        
        if (!$update->execute()) {
            throw new Exception('Database update failed');
        }

        // Return success
        echo json_encode(['success' => true]);
        exit();

    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>