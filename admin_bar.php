<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- For Icons -->
    <style>
        body {
            background-color: #121212;
            color: #f5f5f5;
            font-family: 'Arial', sans-serif;
            display: flex;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #2c3e50;
            color: #ecf0f1;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            color: #ecf0f1;
            padding: 15px;
            text-decoration: none;
            font-size: 1.1rem;
            border-bottom: 1px solid #34495e;
        }
        .sidebar a:hover {
            background-color: #34495e;
            color: #fff;
        }
        .sidebar .active {
            background-color: #1abc9c;
            color: #fff;
        }
        .sidebar-header {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 30px;
            font-weight: bold;
            color: #1abc9c;
        }
        .content {
            margin-left: 250px;
            padding: 30px;
            width: 100%;
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
        .table {
            background-color: #34495e;
            border-radius: 10px;
            color: #ecf0f1;
        }
        .table th, .table td {
            text-align: center;
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
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            Admin Panel
        </div>
        <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="admin_licenses.php"><i class="fas fa-file-alt"></i>Licenses</a>
        <a href="admin_complaints.php"><i class="fas fa-headset"></i>Customer Support</a>
        <a href="admin_reports.php"><i class="fas fa-headset"></i>Reports</a>
        <a href="admin_reviews.php"><i class="fas fa-star"></i> Manage Reviews</a>
        <a href="admin_check_riders.php"><i class="fas fa-wrench"></i>Vehicle Maintenance</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
