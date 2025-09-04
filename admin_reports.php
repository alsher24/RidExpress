<?php
session_start();
include 'db_connection1.php'; // Adjust the path as needed
include 'admin_bar.php'; 

// Safe way to get the values from $_GET and assign defaults
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// Start building the base SQL query
$sql = "SELECT rr.*, 
            u.full_name AS user_full_name, 
            r.first_name AS rider_first, r.last_name AS rider_last 
        FROM rider_reports rr
        JOIN users u ON rr.user_id = u.id
        JOIN riders r ON rr.rider_id = r.id
        WHERE 1";

// Prepare the parameters for binding
$params = [];
$types = "";

// Apply filters if any
if ($status_filter !== "") {
    $sql .= " AND rr.status = ?";
    $params[] = $status_filter;
    $types .= "s"; // String type for status
}

if ($keyword !== "") {
    $sql .= " AND rr.report_text LIKE ?";
    $params[] = "%$keyword%";
    $types .= "s"; // String type for report text
}

// Add ordering
$sql .= " ORDER BY rr.created_at DESC";

// Prepare the statement only once
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error); // Shows exact SQL error
}

// Bind parameters only if we have any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Rider Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
/* Global Styles */
body {
    background-color: #121212;
    color: #f5f5f5;
    font-family: 'Arial', sans-serif;
    margin-left: 250px;
}

.container {
    margin-top: 50px;
}

.card {
    background-color: #2c3e50;  /* Dark card background */
    border-radius: 15px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
    margin-bottom: 20px;
    color: #ffffff;  /* White text color to make the text visible */
}

.card-header {
    background-color: #34495e;
    border-radius: 15px 15px 0 0;
    text-align: center;
    padding: 20px;
    font-size: 1.5rem;
    font-weight: bold;
    color: #ecf0f1;  /* Light text in the header */
}

.card-body {
    padding: 20px;
    color: #ffffff;  /* White text color inside card body */
}

h2 {
    color: #ecf0f1;
    font-size: 2rem;
    font-weight: bold;
}

.badge {
    text-transform: capitalize;
}

.filter-form {
    margin-bottom: 20px;
    background-color: #34495e;
    padding: 20px;
    border-radius: 10px;
}

.filter-form .form-select, .filter-form .form-control {
    background-color: #2c3e50;
    color: #ecf0f1;
    border: 1px solid #34495e;
}

.filter-form button {
    background-color: #2ecc71;
    border-radius: 5px;
    font-size: 1rem;
}

.filter-form button:hover {
    background-color: #27ae60;
}

.filter-form a {
    background-color: #e74c3c;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    padding: 6px 15px;
}

.filter-form a:hover {
    background-color: #c0392b;
}

.form-select, .form-control {
    border-radius: 5px;
    padding: 8px;
    margin-top: 5px;
}

/* Report Cards */
.card-title {
    font-size: 1.3rem;
    font-weight: bold;
    color: #ffffff;  /* White color for the report title */
}

.card-title .badge {
    font-size: 1rem;
    padding: 5px 10px;
}

.card-body p {
    font-size: 1rem;
    margin-bottom: 10px;
    color: #ffffff;  /* Ensuring text in the report section is white */
}

.card-body p small {
    color: #bdc3c7;  /* Lighter text for the timestamp, to make it less prominent */
}

/* Admin Actions */
form button {
    background-color: #2ecc71;
    border-radius: 5px;
    padding: 5px 15px;
    font-size: 1rem;
}

form button:hover {
    background-color: #27ae60;
}

/* Mobile and Table Responsiveness */
@media (max-width: 768px) {
    .container {
        margin-left: 0;
    }

    .card-body {
        padding: 10px;
    }

    .filter-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .filter-form .col-md-4 {
        width: 100%;
    }

    .filter-form button, .filter-form a {
        width: 100%;
        text-align: center;
    }

    .card-title {
        font-size: 1.1rem;
    }

    .form-select, .form-control {
        font-size: 0.9rem;
    }
}

    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">Rider Reports</h2>

    <form method="GET" class="row g-3 filter-form">
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">-- Filter by Status --</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="reviewed" <?= $status_filter == 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                <option value="resolved" <?= $status_filter == 'resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" name="keyword" class="form-control" placeholder="Search reports..." value="<?= htmlspecialchars($keyword) ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="admin_reports.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <?php while ($report = $result->fetch_assoc()): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Report #<?= $report['id'] ?> 
                    <span class="badge bg-<?= $report['status'] === 'pending' ? 'warning' : ($report['status'] === 'reviewed' ? 'info' : 'success') ?>">
                        <?= ucfirst($report['status']) ?>
                    </span>
                </h5>
                <p><strong>User:</strong> <?= htmlspecialchars($report['user_full_name']) ?></p>
                <p><strong>Rider:</strong> <?= htmlspecialchars($report['rider_first'] . ' ' . $report['rider_last']) ?></p>
                <p><strong>Report:</strong> <?= nl2br(htmlspecialchars($report['report_text'])) ?></p>
                <p class="text-muted"><small>Submitted on <?= date("M j, Y g:i A", strtotime($report['created_at'])) ?></small></p>

                <!-- Admin Actions -->
                <?php if ($report['status'] != 'resolved'): ?>
                    <form method="POST" action="update_report_status.php" class="d-inline">
                        <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                        <select name="new_status" class="form-select d-inline w-auto">
                            <option value="reviewed">Mark as Reviewed</option>
                            <option value="resolved">Mark as Resolved</option>
                        </select>
                        <button type="submit" class="btn btn-success btn-sm">Update</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
