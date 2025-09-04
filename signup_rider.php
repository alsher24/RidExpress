<?php 
include 'db.php';

if (isset($_POST['signup'])) {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $age = trim($_POST['age']);
    $gender = trim($_POST['gender']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $vehicle_type = trim($_POST['vehicle_type']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location='signup_rider.php';</script>";
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Corrected SQL query (excluding license_number)
    $sql = "INSERT INTO riders (last_name, first_name, middle_name, age, gender, contact_number, address, vehicle_type, email, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // Check if the statement was prepared successfully
    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("ssssssssss", $last_name, $first_name, $middle_name, $age, $gender, $contact_number, $address, $vehicle_type, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Rider registration successful! You can now log in.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.location='signup_rider.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #0f071f;
            color: #dfd3fa;
            font-family: 'Open Sans', sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            background-color: #2c394b;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            background-color: #1b1f3b;
            border-radius: 15px 15px 0 0;
            text-align: center;
            padding: 20px;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .form-control {
            background-color: #1b1f3b;
            border: 1px solid #9287ac;
            color: #dfd3fa;
        }

        .form-control:focus {
            background-color: #25294b;
            border-color: #dfd3fa;
            color: white;
            box-shadow: none;
        }

        .btn-primary {
            background-color: #9287ac;
            border: none;
            font-weight: bold;
            padding: 10px;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background-color: #dfd3fa;
            color: #2c394b;
        }

        .text-center a {
            color: #dfd3fa;
            text-decoration: none;
        }

        .text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-bicycle"></i> Rider Sign Up
                    </div>
                    <div class="card-body">
                        <form action="signup_rider.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Age</label>
                                <input type="number" name="age" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-control" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" required>
                            </div>
                           
                            <div class="mb-3">
                                <label class="form-label">Vehicle Type</label>
                                <select name="vehicle_type" class="form-control" required>
                                    <option value="Motorcycle">Motorcycle</option>
                                    <option value="Taxi">Taxi</option>
                                    <option value="Van">Van</option>
                                    <option value="Jeep">Jeep</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="signup" class="btn btn-primary">
                                    <i class="bi bi-check-circle-fill"></i> Sign Up
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Already have an account? <a href="login.php">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
