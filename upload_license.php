<?php
session_start();
include 'db.php'; // Database connection file
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to upload your driver's license.";
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check the status of the user's license from the database
$sql = "SELECT status FROM rider_licenses WHERE rider_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

// Determine the message based on the status
if ($status === "Pending") {
    $status_message = "WAIT FOR ADMIN APPROVAL";
    $status_class = "alert-warning";
    $show_upload_form = false; // Don't show upload button if status is pending
} elseif ($status === "Accepted") {
    $status_message = "CONGRATULATIONS, YOU'RE NOW READY!";
    $status_class = "alert-success";
    $show_upload_form = false; // Don't show upload button if status is accepted
} elseif ($status === "Rejected") {
    $status_message = "DOCUMENT REJECTED. PLEASE UPLOAD AGAIN.";
    $status_class = "alert-danger";
    $show_upload_form = true; // Show upload form if status is rejected
} else {
    $status_message = "No document uploaded yet.";
    $status_class = "alert-info";
    $show_upload_form = true; // Show upload form if no document is uploaded
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["license"]) && $show_upload_form) {
    $target_dir = "uploads/licenses/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["license"]["name"]);
    $unique_name = time() . "_" . $file_name;
    $target_file = $target_dir . $unique_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed = ["jpg", "jpeg", "png", "pdf"];
    if (in_array($file_type, $allowed)) {
        if (move_uploaded_file($_FILES["license"]["tmp_name"], $target_file)) {
            // Prepare the SQL statement
            $sql = "INSERT INTO rider_licenses (rider_id, license_path, status) VALUES (?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                echo "Error preparing statement: " . $conn->error;
                exit;
            }

            // Bind parameters and execute the query
            $stmt->bind_param("is", $user_id, $target_file);
            if ($stmt->execute()) {
                $message = "License uploaded. Awaiting admin approval.";
                $alert_class = "alert-success";
                
                // Redirect to avoid resubmission on refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $message = "Error executing query: " . $stmt->error;
                $alert_class = "alert-danger";
            }

            // Close the statement
            $stmt->close();
        } else {
            $message = "Error uploading file.";
            $alert_class = "alert-danger";
        }
    } else {
        $message = "Invalid file type. Only JPG, PNG, and PDF are allowed.";
        $alert_class = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Driver's License</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #f5f5f5;
            font-family: 'Arial', sans-serif;
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
        .form-control {
            background-color: #34495e;
            border: 1px solid #7f8c8d;
            color: #ecf0f1;
        }
        .form-control:focus {
            background-color: #2c3e50;
            border-color: #ecf0f1;
            color: white;
            box-shadow: none;
        }
        .btn-primary {
            background-color: #2980b9;
            border: none;
            font-weight: bold;
            padding: 10px;
            border-radius: 10px;
        }
        .btn-primary:hover {
            background-color: #3498db;
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #2ecc71;
            color: white;
        }
        .alert-danger {
            background-color: #e74c3c;
            color: white;
        }
        .alert-warning {
            background-color: #f39c12;
            color: white;
        }
        .alert-info {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-upload"></i> Upload Driver's License
                    </div>
                    <div class="card-body">
                        <!-- Display the status message -->
                        <div class="alert <?php echo $status_class; ?>">
                            <?php echo $status_message; ?>
                        </div>

                        <!-- Display upload form if the status is "Rejected" or no document uploaded -->
                        <?php if ($show_upload_form): ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="license" class="form-label">Select License File (JPG, PNG, or PDF)</label>
                                    <input type="file" class="form-control" id="license" name="license" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Upload License
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="dashboard1.php" class="text-decoration-none text-light">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
