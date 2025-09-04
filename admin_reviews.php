<?php
include 'db_connection1.php'; 
include 'admin_bar.php'; 
session_start();

$sql = "
    SELECT 
        rv.id AS review_id,
        rv.rating,
        rv.comment,
        rv.created_at,
        rd.first_name AS rider_fname,
        rd.last_name AS rider_lname,
        rd.vehicle_type,
        u.full_name AS user_name
    FROM reviews rv
    JOIN ride_requests rr ON rv.ride_request_id = rr.id
    JOIN riders rd ON rr.rider_id = rd.id
    JOIN users u ON rv.user_id = u.id
    ORDER BY rv.created_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL error: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - All Rider Reviews</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #f5f5f5;
            font-family: 'Arial', sans-serif;
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            background-color: #2c3e50;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }
        .card-header {
            background-color: #34495e;
            font-size: 1.5rem;
            font-weight: bold;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .table {
            background-color: #34495e;
            color: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            background-color: #1c2833;
            color: #ffffff;
        }
        .table td, .table th {
            vertical-align: middle;
            text-align: center;
        }
        .badge-rating {
            background-color: #f39c12;
            font-size: 1rem;
        }
        .badge-rating.high {
            background-color: #2ecc71;
        }
        .badge-rating.low {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">All Rider Reviews</div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Rider</th>
                                <th>Vehicle Type</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date Reviewed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                    $rating = (int)$row['rating'];
                                    $badgeClass = $rating >= 4 ? 'high' : ($rating <= 2 ? 'low' : '');
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['rider_fname'] . ' ' . $row['rider_lname']) ?></td>
                                    <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
                                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                                    <td>
                                        <span class="badge badge-rating <?= $badgeClass ?>">
                                            <?= $row['rating'] ?>/5
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['comment']) ?></td>
                                    <td><?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No reviews found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
