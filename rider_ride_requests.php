<?php
session_start();
require_once 'db.php'; // Your DB connection
include 'navbar1.php'; // Optional navbar

// Check if user is logged in as a rider
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'rider') {
    die("Access denied. Only riders can view this page.");
}

$rider_id = $_SESSION['user_id']; // Current logged-in rider

// Fetch all ride requests assigned to this rider
$stmt = $conn->prepare("
    SELECT ride_requests.*, users.full_name, users.phone_number 
    FROM ride_requests 
    JOIN users ON ride_requests.user_id = users.id 
    WHERE ride_requests.rider_id = ?
    ORDER BY ride_requests.created_at DESC
");
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();
$ride_requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Ride Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #0f071f;
            color: #dfd3fa;
        }
        .card {
            background-color: #2c394b;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h5 {
            color: #0FFF50;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-accepted { background-color: #0d6efd; color: #fff; }
        .status-completed { background-color: #198754; color: #fff; }
        .status-cancelled { background-color: #dc3545; color: #fff; }
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0FFF50;
            margin-right: 15px;
        }
        .ride-details {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .ride-info {
            flex: 1;
        }
        .price-display {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0FFF50;
        }
        .location-icon {
            color: #0FFF50;
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-white mb-4">My Ride Appointments</h2>

    <?php if (!empty($ride_requests)): ?>
        <?php foreach ($ride_requests as $ride): ?>
            <div class="card">
                <div class="ride-details">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRv12rpCJwVnia-jaZ-v1WN3UGCeaxM63wjCg&s" 
                         class="profile-img" 
                         alt="Customer profile">
                    <div class="ride-info">
                        <h5><?= htmlspecialchars($ride['full_name']) ?></h5>
                    </div>
                </div>
                <div class="price-display">₱<?= number_format($ride['price'], 2) ?></div>
<br>
                <div class="row">
                    <div class="col-md-6">
                        <p><i class="fas fa-map-marker-alt location-icon"></i> <strong>Pickup:</strong> <?= htmlspecialchars($ride['pickup_location']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><i class="fas fa-flag-checkered location-icon"></i> <strong>Destination:</strong> <?= htmlspecialchars($ride['destination_location']) ?></p>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="status-badge status-<?= $ride['status'] ?>">
                        <?= ucfirst($ride['status']) ?>
                    </span>
                    
                    <small class="text-muted">
                        <i class="far fa-clock"></i> <?= date('F j, Y g:i A', strtotime($ride['created_at'])) ?>
                    </small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-taxi fa-4x mb-3" style="color: #0FFF50;"></i>
                <h4 class="card-title">No Ride Appointments</h4>
                <p class="card-text">You currently have no scheduled rides.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>