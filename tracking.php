<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$ride_id = $_GET['ride_id'];
$rider_id = $_SESSION['user_id'];

// Get ride details
$stmt = $conn->prepare("SELECT r.*, u.full_name AS customer_name 
                       FROM ride_requests r
                       JOIN users u ON r.user_id = u.id
                       WHERE r.id=? AND r.rider_id=?");
$stmt->bind_param("ii", $ride_id, $rider_id);
$stmt->execute();
$result = $stmt->get_result();
$ride = $result->fetch_assoc();

if (!$ride) {
    die("Ride not found or unauthorized access");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Tracking - RideXpress</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body { margin: 0; padding: 0; 
            font-family: 'Arial', sans-serif;
}
        #map { height: 100vh; width: 100vw; }
        .control-panel {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
        }
    </style>

<style>
.map-control-panel {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: rgba(44, 57, 75, 0.95);
    border-radius: 15px;
    padding: 20px;
    width: 350px;
    max-width: 90%;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 1000;
    backdrop-filter: blur(5px);
    color: #ffffff; /* Default text color inside panel */
}

.map-control-panel *,
.map-control-panel h4,
.map-control-panel h5,
.map-control-panel span {
    color: #ffffff !important; /* Force white text for all nested elements */
}

.map-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.map-stats {
    margin: 15px 0;
}

.stat-card {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.stat-card:last-child {
    border-bottom: none;
}

.stat-icon {
    width: 40px;
    height: 40px;
    background: rgba(15, 255, 80, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: #0bd046; /* Bright green icon */
    font-size: 18px;
}

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #cccccc !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #ffffff !important;
    margin-top: 2px;
}

.map-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-action {
    flex: 1;
    padding: 12px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-complete {
    background-color: #0bd046;
    color: #000;
}

.btn-complete:hover {
    background-color: #0ae03f;
    transform: translateY(-2px);
}

.btn-cancel {
    background-color: rgba(220, 53, 69, 0.2);
    color: #ff4f4f;
    border: 1px solid #ff4f4f;
}

.btn-cancel:hover {
    background-color: rgba(220, 53, 69, 0.3);
    transform: translateY(-2px);
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background-color: rgba(255, 193, 7, 0.2);
    color: #ffc107;
}

.status-accepted {
    background-color: rgba(23, 162, 184, 0.2);
    color: #17a2b8;
}

.status-completed {
    background-color: rgba(15, 255, 80, 0.2);
    color: #0bd046;
}

.text-accent {
    color: #0bd046;
}

@media (max-width: 768px) {
    .map-control-panel {
        width: 90%;
        left: 50%;
        transform: translateX(-50%);
        bottom: 10px;
    }
}
</style>

    
</head>
<body>
<div id="map"></div>
<div class="map-control-panel">
    <div class="map-header">
        <div class="d-flex align-items-center mb-3">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRv12rpCJwVnia-jaZ-v1WN3UGCeaxM63wjCg&s" 
                 class="rounded-circle me-3" 
                 width="50" 
                 height="50" 
                 alt="Customer profile">
            <div>
                <h4 class="mb-0"><?= htmlspecialchars($ride['customer_name']) ?></h4>
                <div class="d-flex align-items-center mt-1">
                    <i class="fas fa-phone-alt me-2"></i>
                    <span>09609097035</span>
                </div>
            </div>
        </div>
        
        <div class="ride-destination mb-3">
            <h5 class="text-accent">
                <i class="fas fa-flag-checkered me-2"></i>
                <?= htmlspecialchars($ride['destination_location']) ?>
            </h5>
        </div>
    </div>
    
    <div class="map-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Distance Remaining</span>
                <span id="distance" class="stat-value">Calculating...</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Estimated Time</span>
                <span id="eta" class="stat-value">Calculating...</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Status</span>
                <span id="status" class="stat-value badge status-<?= strtolower(htmlspecialchars($ride['status'])) ?>">
                    <?= htmlspecialchars($ride['status']) ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="map-actions">
        <button onclick="completeRide()" class="btn-action btn-complete">
            <i class="fas fa-check-circle me-2"></i> Complete Ride
        </button>
        <button onclick="cancelRide()" class="btn-action btn-cancel">
            <i class="fas fa-times-circle me-2"></i> Cancel Ride
        </button>
    </div>
</div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map, riderMarker, routeLayer;
        let watchId;
        const rideId = <?= $ride_id ?>;
        
        // Map initialization
        function initMap() {
            map = L.map('map').setView([<?= $ride['pickup_lat'] ?>, <?= $ride['pickup_lng'] ?>], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Add pickup and destination markers
            L.marker([<?= $ride['pickup_lat'] ?>, <?= $ride['pickup_lng'] ?>])
                .bindPopup('Pickup Location')
                .addTo(map);
                
            L.marker([<?= $ride['destination_lat'] ?>, <?= $ride['destination_lng'] ?>])
                .bindPopup('Destination')
                .addTo(map);

            // Start tracking rider's position
            startTracking();
        }

        // Real-time position tracking
        function startTracking() {
            if (!navigator.geolocation) {
                alert("Geolocation is not supported by your browser.");
                return;
            }

            watchId = navigator.geolocation.watchPosition(
                position => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Update rider marker
                    if (!riderMarker) {
                        riderMarker = L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'rider-icon',
                                html: '🏍️',
                                iconSize: [30, 30]
                            })
                        }).addTo(map);
                    } else {
                        riderMarker.setLatLng([lat, lng]);
                    }

                    // Update map view
                    map.setView([lat, lng]);
                    
                    // Update route and ETA
                    updateRouteAndETA(lat, lng);
                },
                error => {
                    console.error("Geolocation error:", error);
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 5000
                }
            );
        }

        // Update route and calculations
        async function updateRouteAndETA(lat, lng) {
            try {
                // Get route from current position to destination
                const response = await fetch(
                    `https://router.project-osrm.org/route/v1/driving/${lng},${lat};${<?= $ride['destination_lng'] ?>},${<?= $ride['destination_lat'] ?>}?overview=full&geometries=geojson`
                );
                
                const data = await response.json();
                
                if (data.routes && data.routes.length > 0) {
                    const route = data.routes[0];
                    
                    // Update route display
                    if (routeLayer) map.removeLayer(routeLayer);
                    routeLayer = L.geoJSON(route.geometry).addTo(map);
                    
                    // Update ETA and distance
                    document.getElementById('distance').textContent = 
                        `${(route.distance / 1000).toFixed(1)} km`;
                    document.getElementById('eta').textContent = 
                        `${Math.ceil(route.duration / 60)} minutes`;
                }
            } catch (error) {
                console.error("Error updating route:", error);
            }
        }

        // Complete ride function
        async function completeRide() {
            try {
                const response = await fetch(`complete_ride.php?ride_id=${rideId}`);
                const result = await response.json();
                
                if (result.success) {
                    alert('Ride completed successfully!');
                    window.location.href = 'dashboard1.php';
                }
            } catch (error) {
                console.error("Error completing ride:", error);
            }
        }

        // Initialize map when page loads
        initMap();

        // Cleanup when leaving page
        window.addEventListener('beforeunload', () => {
            if (watchId) navigator.geolocation.clearWatch(watchId);
        });
    </script>
</body>
</html>