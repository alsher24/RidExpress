<?php
session_start();
include 'db.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// First, check if the connection is working
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Enhanced query to fetch completed rides without reviews
$sql = "
    SELECT 
        rr.*,
        CONCAT(rd.first_name, ' ', rd.last_name) AS rider_name,
        rd.vehicle_type
    FROM ride_requests rr
    LEFT JOIN reviews rv ON rr.id = rv.ride_request_id AND rv.user_id = rr.user_id
    JOIN riders rd ON rr.rider_id = rd.id
    WHERE rr.user_id = ? 
    AND rr.status = 'completed' 
    AND rv.id IS NULL
    ORDER BY rr.created_at DESC
";


// Prepare the statement and check for errors
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters and execute
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Completed Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #0f071f;
            color: #dfd3fa;
            font-family: 'Open Sans', sans-serif;
        }
        .container {
            margin-top: 50px;
            max-width: 800px;
        }
        .card {
            background-color: #2c394b;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.3);
            border: 1px solid #3a4a5d;
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
        .stars input {
            display: none;
        }
        .stars label {
            float: right;
            padding: 5px;
            font-size: 30px;
            color: #444;
            transition: 0.3s;
            cursor: pointer;
        }
        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label {
            color: gold;
        }
        textarea {
            resize: none;
            background-color: #1a2232;
            color: #dfd3fa;
            border: 1px solid #3a4a5d;
        }
        .rider-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #3a4a5d;
        }
        .rider-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #6c63ff;
        }
        .rider-details h5 {
            margin-bottom: 5px;
            color: #dfd3fa;
        }
        .rider-details p {
            margin-bottom: 0;
            color: #a0a8c0;
        }
        .btn-submit {
            background: linear-gradient(135deg, #6c63ff, #4d44db);
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #4d44db, #6c63ff);
            transform: translateY(-2px);
        }
        .location-icon {
            color: #6c63ff;
            margin-right: 8px;
        }
        .no-reviews {
            background-color: #2c394b;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            border: 1px dashed #3a4a5d;
        }
        .no-reviews i {
            font-size: 50px;
            color: #6c63ff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="text-center mb-4">Your Completed Bookings</h3>
    
    <?php if ($result->num_rows > 0): ?>
        <?php while ($ride = $result->fetch_assoc()): ?>
            <div class="card">
                <!-- Rider Information -->
                <div class="rider-info">
                <img src="<?= !empty($ride['profile_picture']) ? htmlspecialchars($ride['profile_picture']) : 'https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png' ?>" 
     alt="Rider" class="rider-avatar">

                    <div class="rider-details">
                        <h5><?= htmlspecialchars($ride['rider_name']) ?></h5>
                        <p><i class="fas fa-motorcycle"></i> <?= htmlspecialchars($ride['vehicle_type']) ?></p>
                    </div>
                </div>

                <!-- Ride Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><i class="fas fa-map-marker-alt location-icon"></i> <strong>Pick-up:</strong><br>
                        <?= htmlspecialchars($ride['pickup_location']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><i class="fas fa-flag-checkered location-icon"></i> <strong>Destination:</strong><br>
                        <?= htmlspecialchars($ride['destination_location']) ?></p>
                    </div>
                </div>

                <p><i class="far fa-calendar-alt"></i> <strong>Date Completed:</strong> 
                <?= date('M j, Y g:i A', strtotime($ride['created_at'])) ?></p>

                <?php if (isset($ride['price'])): ?>
                    <div class="price-badge">
                        <i class="fas fa-money-bill-wave"></i> Total Price: ₱<?= number_format($ride['price'], 2) ?>
                    </div>
                <?php endif; ?>

                <hr>
                
                <!-- Review Form -->
                <form action="submit_review.php" method="POST" class="review-form">
                    <input type="hidden" name="ride_request_id" value="<?= $ride['id'] ?>">
                    <input type="hidden" name="rider_id" value="<?= $ride['rider_id'] ?>">

                    <div class="mb-4 text-center">
                        <label class="form-label"><strong>Rate Your Rider</strong></label>
                        <div class="stars">
                            <input type="radio" name="rating" id="star5_<?= $ride['id'] ?>" value="5" required>
                            <label for="star5_<?= $ride['id'] ?>">★</label>
                            <input type="radio" name="rating" id="star4_<?= $ride['id'] ?>" value="4">
                            <label for="star4_<?= $ride['id'] ?>">★</label>
                            <input type="radio" name="rating" id="star3_<?= $ride['id'] ?>" value="3">
                            <label for="star3_<?= $ride['id'] ?>">★</label>
                            <input type="radio" name="rating" id="star2_<?= $ride['id'] ?>" value="2">
                            <label for="star2_<?= $ride['id'] ?>">★</label>
                            <input type="radio" name="rating" id="star1_<?= $ride['id'] ?>" value="1">
                            <label for="star1_<?= $ride['id'] ?>">★</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="review" class="form-label"><strong>Write a Review</strong></label>
                        <textarea name="comment" class="form-control" rows="3" 
                                  placeholder="How was your ride experience?" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-submit w-100">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-reviews">
            <i class="far fa-check-circle"></i>
            <h4>No Reviews Pending</h4>
            <p>You have no completed bookings that require your review.</p>
            <a href="bookings.php" class="btn btn-submit mt-3">View Your Bookings</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Add animation when submitting review
    document.querySelectorAll('.review-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
        });
    });
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>