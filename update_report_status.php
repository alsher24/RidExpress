<?php
include 'db_connection1.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = $_POST['report_id'];
    $new_status = $_POST['new_status'];

    if (in_array($new_status, ['reviewed', 'resolved'])) {
        $stmt = $conn->prepare("UPDATE rider_reports SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $report_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: admin_reports.php");
exit();
