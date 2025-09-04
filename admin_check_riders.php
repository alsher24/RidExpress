<?php
// admin_check_riders.php
include 'db_connection1.php';
include 'admin_bar.php';
session_start();

// Process rider check form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rider_ids'])) {
    $ids = $_POST['rider_ids'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "UPDATE riders SET is_checked = 1, last_checked_at = NOW(), next_check_at = DATE_ADD(NOW(), INTERVAL 60 DAY) WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();

    header('Location: admin_check_riders.php?success=1');
    exit();
}

// Filtering inputs
$nameFilter = $_GET['name'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Base SQL
$sql = "SELECT * FROM riders WHERE 1=1";

// Apply filters
$params = [];
$types = '';

if (!empty($nameFilter)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
    $params[] = "%$nameFilter%";
    $params[] = "%$nameFilter%";
    $types .= 'ss';
}

if ($statusFilter === 'checked') {
    $sql .= " AND is_checked = 1";
} elseif ($statusFilter === 'not_checked') {
    $sql .= " AND (is_checked = 0 OR last_checked_at IS NULL)";
}

if (!empty($dateFrom)) {
    $sql .= " AND last_checked_at >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if (!empty($dateTo)) {
    $sql .= " AND last_checked_at <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

$sql .= " ORDER BY last_name ASC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Rider Check</title>
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
        .form-control, .form-select {
            background-color: #1b1f3b;
            border: 1px solid #9287ac;
            color: #dfd3fa;
        }
        .form-control:focus, .form-select:focus {
            background-color: #25294b;
            color: white;
            box-shadow: none;
            border-color: #dfd3fa;
        }
        .btn-primary, .btn-success {
            font-weight: bold;
            border-radius: 10px;
        }
        .btn-primary {
            background-color: #9287ac;
            border: none;
        }
        .btn-primary:hover {
            background-color: #dfd3fa;
            color: #2c3e50;
        }
        .btn-success {
            background-color: #2ecc71;
            border: none;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .table-warning {
            background-color: #ffdd57 !important;
            color: #2c3e50 !important;
        }
        .alert {
            background-color: #27ae60;
            color: white;
            text-align: center;
            border-radius: 5px;
            padding: 10px;
        }
        .status-checked {
    color: #2ecc71; /* green */
    font-weight: bold;
}

.status-expired {
    color: #f1c40f; /* yellow */
    font-weight: bold;
}

    </style>
</head>
<body style="margin-left: 250px; padding: 20px;">


<div class="container">
<div class="card">
        <div class="card-header">Vehicle Maintenance Check</div>
        <div class="card-body">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert">Riders successfully marked as checked.</div>
            <?php endif; ?>


    <!-- Filter Form -->
    <form class="row g-3 mb-4" method="get">
        <div class="col-md-3">
            <input type="text" class="form-control" name="name" placeholder="Search Name" value="<?= htmlspecialchars($nameFilter) ?>">
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">All Status</option>
                <option value="checked" <?= $statusFilter === 'checked' ? 'selected' : '' ?>>Checked</option>
                <option value="not_checked" <?= $statusFilter === 'not_checked' ? 'selected' : '' ?>>Not Checked</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <form method="post">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
            <tr>
                <th>Select</th>
                    <th>Profile</th>

                <th>Full Name</th>
                <th>Vehicle</th>
                <th>Last Checked</th>
                <th>Next Check</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($rider = $result->fetch_assoc()): ?>
                <?php
                    $checked = $rider['is_checked'];
                    $lastChecked = $rider['last_checked_at'];
                    $nextCheck = $rider['next_check_at'];
                    $lastCheckedText = $lastChecked ? date("F j, Y", strtotime($lastChecked)) : 'Never';
                    $nextCheckText = $nextCheck ? date("F j, Y", strtotime($nextCheck)) : 'Not Set';
                    $status = (!$checked || strtotime($lastChecked) < strtotime('-2 months')) ? 'Expired / Not Checked' : 'Checked';
                    $expired = $status !== 'Checked';
                ?>
           <tr class="<?= $expired ? 'table-warning' : '' ?>">
    <td>
        <?php if ($expired): ?>
            <input type="checkbox" name="rider_ids[]" value="<?= $rider['id'] ?>">
        <?php endif; ?>
    </td>
    <td>
        <img src="https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png" 
             alt="Rider Profile" 
             style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
    </td>
    <td><?= htmlspecialchars($rider['first_name'] . ' ' . $rider['last_name']) ?></td>
    <td><?= htmlspecialchars($rider['vehicle_type']) ?></td>
    <td><?= $lastCheckedText ?></td>
    <td><?= $nextCheckText ?></td>
    <td class="<?= $expired ? 'status-expired' : 'status-checked' ?>">
    <?= $status ?>
</td>
</tr>

            <?php endwhile; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Mark Selected as Checked</button>
    </form>
</div>
</body>
</html>
