<?php
session_start();
require_once 'db.php';
include 'navbar1.php';

// Check if user is logged in as a rider
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'rider') {
    die("You must be logged in as a rider to view bookings.");
}

// Fetch the rider's information
$rider_id = $_SESSION['user_id'];
$rider = [];

$stmt = $conn->prepare("SELECT * FROM riders WHERE id = ?");
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$rider_result = $stmt->get_result();
if ($rider_result->num_rows > 0) {
    $rider = $rider_result->fetch_assoc();
}
$stmt->close();

// Handle the rate update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rate'])) {
    $rate_type = $_POST['rate_type'];
    $rate_amount = $_POST['rate_amount'];

    // Update the rider's rate in the database
    $stmt = $conn->prepare("UPDATE riders SET rate_type = ?, rate_amount = ? WHERE id = ?");
    $stmt->bind_param("sdi", $rate_type, $rate_amount, $rider_id);
    $stmt->execute();
    $stmt->close();

    // Refresh the rider's info after update
    $stmt = $conn->prepare("SELECT * FROM riders WHERE id = ?");
    $stmt->bind_param("i", $rider_id);
    $stmt->execute();
    $rider_result = $stmt->get_result();
    if ($rider_result->num_rows > 0) {
        $rider = $rider_result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch bookings assigned to this rider
$bookings = [];
$stmt = $conn->prepare("SELECT rentals.*, users.full_name, users.email, users.phone_number, users.location, riders.profile_picture 
                        FROM rentals 
                        INNER JOIN users ON rentals.user_id = users.id
                        INNER JOIN riders ON rentals.rider_id = riders.id
                        WHERE rentals.rider_id = ?");

$stmt->bind_param("i", $rider_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rider Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f071f;
            color: #dfd3fa;
            font-family: 'Open Sans', sans-serif;
        }
        .card {
            background-color: #2c394b;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            color: #dfd3fa;
        }
        .btn-custom {
            background-color: #0FFF50;
            color: #000;
            font-weight: bold;
            border-radius: 10px;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: #0cc540;
            color: #000;
        }
        .price-badge {
            background-color: #0f071f;
            color: #0FFF50;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin: 8px 0;
            border: 2px solid #0FFF50;
        }
        .card-header {
            background-color: #1b2535;
            color: #fff;
            padding: 10px;
            border-radius: 10px;
            font-size: 1.2rem;
        }
        .card-body {
            font-size: 1rem;
        }
        .action-btns button {
            width: 48%;
            margin-right: 4%;
        }
        .action-btns button:last-child {
            margin-right: 0;
        }
        .rate-form {
            margin-top: 20px;
            background-color: #3b4d6b;
            border-radius: 10px;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h3 class="text-white mb-4">Rental Appointments</h3>
    
    <!-- Rider's Rate Section -->
    <div class="card">
        <div class="card-header">
            <h5>Your Rate Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Rate Type:</strong> <?= ucfirst($rider['rate_type']) ?></p>
            <p><strong>Rate Amount:</strong> ₱<?= number_format($rider['rate_amount'], 2) ?></p>
            
            <!-- Edit Rate Button -->
            <button class="btn btn-warning" data-bs-toggle="collapse" data-bs-target="#editRateForm">Edit Rate</button>
            
            <!-- Edit Rate Form -->
            <div id="editRateForm" class="collapse mt-3 rate-form">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="rate_type" class="form-label">Rate Type</label>
                        <select class="form-select" id="rate_type" name="rate_type" required>
                            <option value="hour" <?= $rider['rate_type'] == 'hour' ? 'selected' : '' ?>>Hourly</option>
                            <option value="day" <?= $rider['rate_type'] == 'day' ? 'selected' : '' ?>>Daily</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rate_amount" class="form-label">Rate Amount</label>
                        <input type="number" class="form-control" id="rate_amount" name="rate_amount" value="<?= $rider['rate_amount'] ?>" required>
                    </div>
                    <button type="submit" name="update_rate" class="btn btn-success">Update Rate</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bookings -->
    <?php if (!empty($bookings)): ?>
        <div class="row">
            <?php foreach ($bookings as $booking): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                    <div class="card-header d-flex align-items-center">
    <img src="<?= !empty($booking['profile_picture']) ? $booking['profile_picture'] : 'https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png' ?>"
         alt="Profile"
         class="rounded-circle me-3"
         style="width: 40px; height: 40px; object-fit: cover;">
    <div>
        <strong><?= htmlspecialchars($booking['full_name']) ?></strong> - <?= htmlspecialchars($booking['vehicle_type']) ?>
    </div>
</div>


                        <div class="card-body">
                            <p><strong>Start Time:</strong> <?= htmlspecialchars($booking['start_time']) ?></p>
                            <p><strong>Duration:</strong> <?= htmlspecialchars($booking['duration_value']) . ' ' . ucfirst($booking['duration_type']) ?></p>
                            <p><strong>Total Cost:</strong> ₱<?= number_format($booking['total_cost'], 2) ?></p>
                            <p><strong>Status:</strong> <?= isset($booking['status']) ? ucfirst($booking['status']) : 'Pending' ?></p>
                            
                            <div class="action-btns">
                                <?php if (!isset($booking['status']) || $booking['status'] === 'pending'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="rental_id" value="<?= $booking['id'] ?>">
                                        <button type="submit" name="confirm_booking" value="confirmed" class="btn btn-custom">Confirm Booking</button>
                                        <button type="submit" name="confirm_booking" value="declined" class="btn btn-danger">Decline Booking</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">No action needed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-white">You have no pending bookings at the moment.</p>
    <?php endif; ?>
</div>

</body>
</html>
