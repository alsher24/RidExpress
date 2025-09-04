<?php
session_start();
include 'db.php'; 

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input and sanitize
    $location = trim($_POST['location']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    // Validate that required fields are not empty
    if (empty($location) || empty($latitude) || empty($longitude)) {
        die("<script>alert('Error: Location details are missing!'); window.history.back();</script>");
    }

    // Check if the user exists
    $check_user = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($check_user);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        die("<script>alert('Error: User not found!'); window.location='login.php';</script>");
    }
    $stmt->close();

    // Update user's location
    $sql = "UPDATE users SET location = ?, latitude = ?, longitude = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("<script>alert('Database Error: " . $conn->error . "'); window.history.back();</script>");
    }

    $stmt->bind_param("sssi", $location, $latitude, $longitude, $user_id);

    if ($stmt->execute()) {
        $_SESSION['location_set'] = true;
        echo "<script>alert('Location saved successfully! Redirecting to dashboard...'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error saving location. Please try again.'); window.history.back();</script>";
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
    <title>Set Location</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
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

        #map {
            height: 300px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .loading {
            display: none;
            color: #dfd3fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <h3>Set Your Location</h3>
                    <p class="loading">Fetching your location... Please wait.</p>
                    <div id="map"></div>
                    <form method="POST">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <div class="mb-3">
                            <label class="form-label">Your Current Location</label>
                            <input type="text" name="location" id="location" class="form-control" required readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Location</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let map = L.map('map').setView([0, 0], 13);
        let marker;

        // Load OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        function getUserLocation() {
            if (navigator.geolocation) {
                document.querySelector('.loading').style.display = 'block';
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;

            document.getElementById("latitude").value = lat;
            document.getElementById("longitude").value = lng;

            map.setView([lat, lng], 15);

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }

            // Convert GPS to address using OpenStreetMap Nominatim API
            fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("location").value = data.display_name;
                    document.querySelector('.loading').style.display = 'none';
                })
                .catch(() => alert("Could not fetch address."));
        }

        function showError(error) {
            document.querySelector('.loading').style.display = 'none';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    alert("User denied the request for Geolocation.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Location information is unavailable.");
                    break;
                case error.TIMEOUT:
                    alert("The request to get user location timed out.");
                    break;
                case error.UNKNOWN_ERROR:
                    alert("An unknown error occurred.");
                    break;
            }
        }

        window.onload = getUserLocation;
    </script>
</body>
</html>
