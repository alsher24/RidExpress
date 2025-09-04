<?php
session_start();
require_once 'db.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to rent a vehicle.");
}

// Booking Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['book_rider'])) {
    $user_id = $_SESSION['user_id'];
    $rider_id = $_POST['rider_id'];
    $vehicle_type = $_POST['vehicle_type'];
    $start_time = $_POST['start_time'];
    $duration_type = $_POST['duration_type'];
    $duration_value = $_POST['duration_value'];
    $rate_amount = $_POST['rate_amount'];
    $rate_type = $_POST['rate_type'];

    if ($rate_type === $duration_type) {
        $total_cost = $rate_amount * $duration_value;
        $stmt = $conn->prepare("INSERT INTO rentals 
            (user_id, rider_id, vehicle_type, start_time, duration_type, duration_value, total_cost) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssid", $user_id, $rider_id, $vehicle_type, $start_time, $duration_type, $duration_value, $total_cost);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Booking confirmed! Total cost: ₱" . number_format($total_cost, 2) . "');</script>";
    } else {
        echo "<script>alert('Rider only accepts " . $rate_type . " rentals.');</script>";
    }
}

// Rider Search
$vehicle_type = $_POST['vehicle_type'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$duration_type = $_POST['duration_type'] ?? '';
$duration_value = $_POST['duration_value'] ?? '';
$riders = [];

if ($vehicle_type && $start_time && $duration_type && $duration_value) {
    $stmt = $conn->prepare("SELECT * FROM riders WHERE vehicle_type = ?");
    $stmt->bind_param("s", $vehicle_type);
    $stmt->execute();
    $riders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent a Vehicle</title>
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
            margin-bottom: 30px;
        }
        label, select, input {
            color: #dfd3fa;
        }
        .form-control, .form-select {
            background-color: #1e2a38;
            color: #fff;
            border: none;
        }
        .form-control:focus, .form-select:focus {
            background-color: #1e2a38;
            color: #fff;
            border: 1px solid #0FFF50;
            box-shadow: none;
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
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <h3 class="text-white mb-4">Step 1: Choose Vehicle & Rental Details</h3>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="vehicle_type" class="form-label">Select Vehicle Type:</label>
                <select name="vehicle_type" class="form-select" required>
                    <option value="">-- Choose --</option>
                    <option value="Motorcycle" <?= ($vehicle_type == 'Motorcycle') ? 'selected' : '' ?>>Motorcycle</option>
                    <option value="Taxi" <?= ($vehicle_type == 'Taxi') ? 'selected' : '' ?>>Taxi</option>
                    <option value="Van" <?= ($vehicle_type == 'Van') ? 'selected' : '' ?>>Van</option>
                    <option value="Jeep" <?= ($vehicle_type == 'Jeep') ? 'selected' : '' ?>>Jeep</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="start_time" class="form-label">Start Time:</label>
                <input type="datetime-local" name="start_time" value="<?= htmlspecialchars($start_time) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="duration_type" class="form-label">Duration Type:</label>
                <select name="duration_type" class="form-select" required>
                    <option value="hour" <?= ($duration_type == 'hour') ? 'selected' : '' ?>>Per Hour</option>
                    <option value="day" <?= ($duration_type == 'day') ? 'selected' : '' ?>>Per Day</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="duration_value" class="form-label">Duration (number of hours/days):</label>
                <input type="number" name="duration_value" min="1" value="<?= htmlspecialchars($duration_value) ?>" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-custom">Find Available Riders</button>
        </form>
    </div>

    <?php if (!empty($riders)): ?>
        <div class="card">
            <h4 class="mb-3">Available Riders for <span class="text-success"><?= htmlspecialchars($vehicle_type) ?></span></h4>
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Rate</th>
                            <th>Rate Type</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riders as $rider): 
                            $total = $rider['rate_type'] === $duration_type ? $rider['rate_amount'] * $duration_value : null;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($rider['first_name'] . ' ' . $rider['last_name']) ?></td>
                            <td>₱<?= number_format($rider['rate_amount'], 2) ?></td>
                            <td><?= ucfirst($rider['rate_type']) ?></td>
                            <td>
                                <?php if ($total !== null): ?>
                                    <span class="price-badge">₱<?= number_format($total, 2) ?></span>
                                <?php else: ?>
                                    <span class="text-warning">Unavailable</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($total !== null): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="book_rider" value="1">
                                        <input type="hidden" name="rider_id" value="<?= $rider['id'] ?>">
                                        <input type="hidden" name="vehicle_type" value="<?= $vehicle_type ?>">
                                        <input type="hidden" name="start_time" value="<?= $start_time ?>">
                                        <input type="hidden" name="duration_type" value="<?= $duration_type ?>">
                                        <input type="hidden" name="duration_value" value="<?= $duration_value ?>">
                                        <input type="hidden" name="rate_type" value="<?= $rider['rate_type'] ?>">
                                        <input type="hidden" name="rate_amount" value="<?= $rider['rate_amount'] ?>">
                                        <button type="submit" class="btn btn-custom">Book Now</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Not Available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
