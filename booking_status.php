<?php
session_start();
include 'db.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all ride requests for the user
$sql = "SELECT * FROM ride_requests WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Request Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #0f071f;
            color: #dfd3fa;
            font-family: 'Open Sans', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            background-color: #2c394b;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary, .btn-success {
            font-weight: bold;
            padding: 10px;
            border-radius: 10px;
            width: 100%;
        }
        .btn-primary:disabled, .btn-success:disabled {
            cursor: not-allowed;
        }
        h3 {
            color: #fff;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            color: #dfd3fa;
            font-size: 16px;
            margin-bottom: 10px;
        }
        strong {
            color: #0FFF50;
        }
        .price-badge {
            background-color: #0f071f;
            color: #0FFF50;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
            border: 2px solid #0FFF50;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin: 5px 0;
        }
        .pending {
            background-color: #FFC107;
            color: #000;
        }
        .accepted {
            background-color: #28A745;
            color: #fff;
        }
        .completed {
            background-color: #007BFF;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($ride_request = $result->fetch_assoc()): ?>
                    <?php
                    $rider_name = "Unavailable";
                    $profile_image = "https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png";

                    if (in_array($ride_request['status'], ['accepted', 'completed']) && !empty($ride_request['rider_id'])) {
                        $rider_id = $ride_request['rider_id'];
                        $rider_stmt = $conn->prepare("SELECT first_name, last_name FROM riders WHERE id = ?");
                        $rider_stmt->bind_param("i", $rider_id);
                        $rider_stmt->execute();
                        $rider_result = $rider_stmt->get_result();
                        if ($rider_row = $rider_result->fetch_assoc()) {
                            $rider_name = $rider_row['first_name'] . ' ' . $rider_row['last_name'];
                        }
                        $rider_stmt->close();
                    }
                    ?>

                    <div class="card">
                        <h3>Your Ride Request Details</h3>

                        <?php if (in_array($ride_request['status'], ['accepted', 'completed'])): ?>
                            <div class="d-flex align-items-center mt-3" style="gap: 15px;">
        <img src="<?= $profile_image ?>" alt="Rider Profile" style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid #fff;">
        <div>
            <p class="mb-0"><strong>Rider Name:</strong><br><?= htmlspecialchars($rider_name) ?></p>
        </div>
    </div>

    <!-- Report Button -->
    <a href="report_rider.php?ride_id=<?= $ride_request['id'] ?>&rider_id=<?= $ride_request['rider_id'] ?>" 
       class="btn btn-outline-danger mt-2" title="Report Rider">
        <i class="fas fa-flag"></i> Report
    </a>
<?php endif; ?>

                        <?php if (isset($ride_request['price'])): ?>
                            <div class="price-badge">
                                Total Price: ₱<?= number_format($ride_request['price'], 2) ?>
                            </div>
                        <?php endif; ?>

                        <div class="row text-start">
                            <div class="col-md-6">
                                <p><strong>Pick-up Location:</strong><br><?= htmlspecialchars($ride_request['pickup_location']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Destination:</strong><br><?= htmlspecialchars($ride_request['destination_location']); ?></p>
                            </div>
                        </div>

                        <p><strong>Request Time:</strong> <?= date('M j, Y g:i A', strtotime($ride_request['created_at'])); ?></p>

                        <div class="status-badge <?= htmlspecialchars($ride_request['status']); ?>">
                            Status: <?= ucfirst(htmlspecialchars($ride_request['status'])); ?>
                        </div>

                        <?php if ($ride_request['status'] == 'pending'): ?>
                            <button class="btn btn-warning mt-3" disabled>Waiting for Driver</button>
                        <?php elseif ($ride_request['status'] == 'accepted'): ?>
                            <button class="btn btn-success mt-3" disabled>Driver On The Way</button>
                        <?php elseif ($ride_request['status'] == 'completed'): ?>
                            <button class="btn btn-primary mt-3" disabled>Ride Completed</button>
                        <?php else: ?>
                            <button class="btn btn-secondary mt-3" disabled>Unknown Status</button>
                        <?php endif; ?>

                     
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <h3>No Ride Requests Yet</h3>
                    <p>You haven't made any ride requests yet.</p>
                    <a href="dashboard.php" class="btn btn-primary">Request a Ride Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
