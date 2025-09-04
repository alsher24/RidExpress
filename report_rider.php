<?php
session_start();
include 'db.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ride_id = $_POST['ride_id'];
    $rider_id = $_POST['rider_id'];
    $report_text = trim($_POST['report_text']);

    if (!empty($report_text)) {
        $stmt = $conn->prepare("INSERT INTO rider_reports (user_id, ride_id, rider_id, report_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $ride_id, $rider_id, $report_text);
        if ($stmt->execute()) {
            $message = "✅ Report submitted successfully. Thank you for helping us keep the platform safe.";
        } else {
            $message = "❌ Error submitting report. Please try again.";
        }
        $stmt->close();
    } else {
        $message = "⚠️ Report text cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Rider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f071f;
            color: #dfd3fa;
            font-family: 'Open Sans', sans-serif;
        }
        .container {
            margin-top: 40px;
            max-width: 600px;
        }
        .card {
            background-color: #2c394b;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        }
        .btn-primary {
            background-color: #6f42c1;
            border: none;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #5936a2;
        }
        textarea {
            resize: none;
            min-height: 120px;
        }
        .alert {
            font-size: 15px;
            margin-bottom: 20px;
        }
        .rider-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        .rider-header img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 2px solid #fff;
        }
        .rider-header h5 {
            margin: 0;
            font-size: 20px;
            color: #ffffff;
        }
        .form-label {
            font-weight: bold;
        }
        .form-text {
            font-size: 14px;
            color: #c5b8e4;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">

        <!-- Rider Avatar and Label -->
        <div class="rider-header">
            <img src="https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png" alt="Rider">
            <div>
                <h5>Report Rider</h5>
                <p class="text-muted mb-0" style="font-size: 14px;">Help us by reporting inappropriate behavior or issues.</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="report_rider.php">
            <input type="hidden" name="ride_id" value="<?= htmlspecialchars($_GET['ride_id'] ?? '') ?>">
            <input type="hidden" name="rider_id" value="<?= htmlspecialchars($_GET['rider_id'] ?? '') ?>">

            <div class="mb-3">
                <label for="report_text" class="form-label">Describe what happened:</label>
                <textarea class="form-control" name="report_text" id="report_text" required></textarea>
                <div class="form-text">Please be as specific as possible. Reports are confidential.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Submit Report</button>
        </form>
    </div>
</div>
</body>
</html>
