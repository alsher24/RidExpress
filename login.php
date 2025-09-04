<?php 
session_start();
include 'db.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check both tables (users for passengers, riders for ride providers)
    $tables = ['users', 'riders'];
    $userFound = false;

    foreach ($tables as $table) {
        $sql = "SELECT * FROM $table WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];

                if ($table === 'users') {
                    $_SESSION['user_type'] = 'user';
                    $_SESSION['full_name'] = $user['full_name'];
                    echo "<script>alert('Login successful!'); window.location='dashboard.php';</script>";
                } else {
                    $_SESSION['user_type'] = 'rider';
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];

                    // ✅ Check license status
                    $license_sql = "SELECT status FROM rider_licenses WHERE rider_id = ? ORDER BY uploaded_at DESC LIMIT 1";
                    $license_stmt = $conn->prepare($license_sql);
                    $license_stmt->bind_param("i", $user['id']);
                    $license_stmt->execute();
                    $license_result = $license_stmt->get_result();

                    $license_status = 'Pending'; // default

                    if ($license_result->num_rows > 0) {
                        $license_data = $license_result->fetch_assoc();
                        $license_status = $license_data['status'];
                    }

                    // Redirect based on license status
                    if ($license_status !== 'Accepted') {
                        echo "<script>
                            alert('Your license has not been accepted. Please upload or wait for approval.');
                            window.location='upload_license.php';
                        </script>";
                    } else {
                        echo "<script>alert('Login successful!'); window.location='dashboard1.php';</script>";
                    }

                    $license_stmt->close();
                }

                $userFound = true;
                break;
            } else {
                $error = "Incorrect password!";
            }
        }
    }

    if (!$userFound) {
        $error = "No account found with this email!";
    }

    $stmt->close();
    $conn->close();
}
?>


<!-- Rest of your HTML remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #0f071f;
            color: #dfd3fa;
            font-family: 'Open Sans', sans-serif;
        }
        .container { margin-top: 50px; }
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
        .alert {
            background-color: #ff4c4c;
            color: white;
            text-align: center;
            border-radius: 5px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { echo "<div class='alert'>$error</div>"; } ?>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-door-open-fill"></i> Login
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Don't have an account? <a href="signup.php">Sign up here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
