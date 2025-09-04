<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
                        <i class="bi bi-person-plus-fill"></i> Sign Up
                    </div>
                    <div class="card-body">
                        <form action="signup_process.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" required>
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
                    </div> <br>
                    <div class="card-footer text-center">
                     <a href="signup_rider.php">Signup as Rider </a>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
