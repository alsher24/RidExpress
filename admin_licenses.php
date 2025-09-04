<?php
include 'db.php';
include 'admin_bar.php'; 

 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["license_id"]) && isset($_POST["action"])) {
    $license_id = $_POST["license_id"];
    $action = $_POST["action"]; // Accept or Reject

    if (in_array($action, ['Accepted', 'Rejected'])) {
        $stmt = $conn->prepare("UPDATE rider_licenses SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $license_id);
        $stmt->execute();
        echo "<div class='alert alert-success'>License status updated to $action.</div>";
    }
}

// Fetch all pending licenses
$sql = "SELECT rl.id, rl.license_path, rl.status, r.first_name, r.last_name FROM rider_licenses rl
        JOIN riders r ON rl.rider_id = r.id ORDER BY rl.uploaded_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Driver's Licenses</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            background-color: #2c3e50;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }
        .card-header {
            background-color: #34495e;
            border-radius: 15px 15px 0 0;
            text-align: center;
            padding: 20px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .table th, .table td {
            text-align: center;
        }
        .table {
            background-color: #34495e;
            border-radius: 10px;
            color: #ecf0f1;
        }
        .btn-accept, .btn-reject {
            padding: 5px 15px;
            font-size: 1rem;
            margin: 5px;
            border-radius: 5px;
            border: none;
        }
        .btn-accept {
            background-color: #2ecc71;
        }
        .btn-accept:hover {
            background-color: #27ae60;
        }
        .btn-reject {
            background-color: #e74c3c;
        }
        .btn-reject:hover {
            background-color: #c0392b;
        }
        .alert {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                Pending Driver's Licenses
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Rider Name</th>
                                <th>License</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></td>
                                    <td><a href="<?= htmlspecialchars($row['license_path']) ?>" target="_blank" class="btn btn-info btn-sm">View License</a></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                                if ($row['status'] == 'Pending') { 
                                                    echo 'bg-warning'; 
                                                } elseif ($row['status'] == 'Accepted') { 
                                                    echo 'bg-success'; 
                                                } else { 
                                                    echo 'bg-danger'; 
                                                } 
                                            ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'Pending'): ?>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="license_id" value="<?= $row['id'] ?>">
                                                <button type="submit" name="action" value="Accepted" class="btn-accept">Accept</button>
                                                <button type="submit" name="action" value="Rejected" class="btn-reject">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <span><?= $row['status'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">No license submissions found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
