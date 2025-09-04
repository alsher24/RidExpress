<?php
session_start();
include 'db.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch active bookings with rider's full name if accepted
$stmt = $conn->prepare("SELECT r.*, 
                        CONCAT(rd.first_name, ' ', rd.last_name) AS rider_full_name
                        FROM ride_requests r
                        LEFT JOIN riders rd ON r.rider_id = rd.id
                        WHERE r.user_id = ? AND r.status IN ('pending', 'accepted')");

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ride Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://nominatim.openstreetmap.org/ui/search.js"></script>
    <link rel="stylesheet" href="https://nominatim.openstreetmap.org/ui/search.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script></style>
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
        .btn-primary, .btn-success {
            font-weight: bold;
            padding: 10px;
            border-radius: 10px;
            width: 100%;
        }
        .btn-success {
            display: none;
        }
        .form-control {
            background-color: #dfd3fa;
            color: #2c394b;
            text-align: center;
            font-weight: bold;
        }
        #mapModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .table-dark {
            background-color: #1a2230;
        }
        .badge {
            font-size: 0.9em;
            padding: 0.6em;
        }
        .bg-warning {
            background-color: #ffc107 !important;
        }

        .selection-marker, .start-marker, .end-marker {
        background: transparent;
        border: none;
    }
    #map {
        transition: all 0.3s ease;
    }
    .leaflet-container {
        background: #2c3e50 !important;
    }

    .transparent-style {
  width: 200px; /* Adjust this value to change the size */
  height: auto; /* This ensures the aspect ratio is maintained */
}


    </style>
</head>
<body>

<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <form method="POST" action="request_ride.php">
                    <div class="mb-3">
                        <label class="form-label">Pick-up Location</label>
                        <input type="text" id="pickup_location" name="pickup_location" class="form-control" placeholder="Click to select location" readonly required onclick="openMap('pickup')">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Destination</label>
                        <input type="text" id="destination_location" name="destination_location" class="form-control" placeholder="Click to select destination" readonly required onclick="openMap('destination')">
                    </div>

                    <input type="hidden" name="pickup_lat" id="pickup_lat">
                    <input type="hidden" name="pickup_lng" id="pickup_lng">
                    <input type="hidden" name="destination_lat" id="destination_lat">
                    <input type="hidden" name="destination_lng" id="destination_lng">
                    <input type="hidden" name="price" id="ride_price">
                    <button type="submit" id="requestRideBtn" class="btn btn-primary" style="display: none;">Request Ride</button>
                </form>
                <button id="viewRouteBtn" class="btn btn-success mt-3" onclick="viewRoute()">View Route</button>
            </div>
        </div>
    </div>

<!-- Enhanced Active Bookings Section - Cards Only -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card booking-card" style="background-color: #1a2230; border: none;">
            <h3 class="text-center mb-4">Your Active Rides</h3>
            
            <?php if (!empty($bookings)): ?>
                <div class="row">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="col-12 col-md-6 col-lg-4 mb-4">
                            <div class="card h-100" style="background-color: #2c394b; border-radius: 10px;">
                                <div class="card-body">
                                    <!-- Rider Name and Profile Picture at Top -->
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png" 
                                             class="rounded-circle me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover;"
                                             alt="Rider Profile">
                                        <div>
                                            <h5 class="card-title mb-0">
                                                <?php if ($booking['status'] === 'accepted' && !empty($booking['rider_full_name'])): ?>
                                                    <?php echo htmlspecialchars($booking['rider_full_name']); ?>
                                                <?php else: ?>
                                                    Rider Not Assigned
                                                <?php endif; ?>
                                            </h5>
                                        </div>
                                    </div>
                                    
                                    <!-- Ride Details -->
                                    <div class="ride-info mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-geo-alt-fill me-2" style="color: #ff6b6b;"></i>
                                            <div>
                                                <small class="text-muted">From</small>
                                                <p class="mb-0"><?php echo htmlspecialchars($booking['pickup_location']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-geo-fill me-2" style="color: #4ecdc4;"></i>
                                            <div>
                                                <small class="text-muted">To</small>
                                                <p class="mb-0"><?php echo htmlspecialchars($booking['destination_location']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <?php if ($booking['status'] === 'accepted'): ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-car-front-fill me-2" style="color: #f9a5ff;"></i>
                                              
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <small class="text-muted">Requested</small>
                                                <p class="mb-0"><?php echo date('M j, g:i a', strtotime($booking['created_at'])); ?></p>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">Price</small>
                                                <p class="mb-0 fw-bold" style="color: #0FFF50;">₱<?php echo number_format($booking['price'], 2); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Badge at Bottom -->
                                    <div class="mt-auto pt-2 border-top text-center">
                                        <span class="badge <?php echo $booking['status'] === 'accepted' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                    <p class="mt-2">No active bookings found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Full-Screen Map Modal -->
<!-- Full-Screen Map Modal -->
<div id="mapModal">
    <div id="mapContainer" style="width: 100%; height: 100%; position: relative;">
        <input type="text" id="searchBar" class="form-control" placeholder="Search location..." 
               style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); width: 80%; max-width: 500px; z-index: 1001;">
        <button id="closeMap" onclick="closeMap()" 
                style="position: absolute; top: 10px; right: 10px; background: red; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 5px; font-size: 16px; z-index: 1001;">X</button>
        <div id="map" style="width: 100%; height: 100%;"></div>
    </div>
</div>

<script>
    let selectedField = "";
    let map, marker, routeLayer;
    let pickupSet = false, destinationSet = false;
    let pickupMarker, destinationMarker;

    function openMap(field) {
        selectedField = field;
        document.getElementById("mapModal").style.display = "flex";
        
        if (!map) {
            setTimeout(() => {
                map = L.map('map').setView([14.5995, 120.9842], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                marker = L.marker(map.getCenter(), {
                    draggable: true,
                    icon: L.divIcon({
                        className: 'selection-marker',
                        html: '<div style="background:#3498db;width:24px;height:24px;border-radius:50%;border:3px solid white"></div>',
                        iconSize: [30, 30]
                    })
                }).addTo(map);
                
                marker.on('dragend', updateLocation);
                
                map.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    updateLocation();
                });

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        map.setView([position.coords.latitude, position.coords.longitude], 15);
                        marker.setLatLng([position.coords.latitude, position.coords.longitude]);
                        updateLocation();
                    });
                }

                setTimeout(() => map.invalidateSize(), 50);
            }, 100);
        } else {
            setTimeout(() => map.invalidateSize(), 50);
        }
    }

    function updateLocation() {
        const latlng = marker.getLatLng();
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${latlng.lat}&lon=${latlng.lng}&format=json`)
            .then(response => response.json())
            .then(data => {
                if (data.display_name) {
                    document.getElementById("searchBar").value = data.display_name;
                    const field = selectedField === "pickup" ? "pickup" : "destination";
                    document.getElementById(`${field}_location`).value = data.display_name;
                    document.getElementById(`${field}_lat`).value = latlng.lat;
                    document.getElementById(`${field}_lng`).value = latlng.lng;
                    
                    if (field === "pickup") pickupSet = true;
                    else destinationSet = true;
                    
                    checkRouteButton();
                }
            });
    }

    function checkRouteButton() {
        document.getElementById("viewRouteBtn").style.display = 
            (pickupSet && destinationSet) ? "block" : "none";
    }

    function closeMap() {
        document.getElementById("mapModal").style.display = "none";
    }

    document.getElementById("searchBar").addEventListener("change", function() {
        const query = this.value.trim();
        if (query.length < 3) return;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    marker.setLatLng([lat, lon]);
                    map.setView([lat, lon], 15);
                    updateLocation();
                }
            });
    });

    function viewRoute() {
        document.getElementById("mapModal").style.display = "flex";
        
        setTimeout(() => {
            map.invalidateSize();
            
            const pickupLat = document.getElementById("pickup_lat").value;
            const pickupLng = document.getElementById("pickup_lng").value;
            const destinationLat = document.getElementById("destination_lat").value;
            const destinationLng = document.getElementById("destination_lng").value;

            if (!pickupLat || !pickupLng || !destinationLat || !destinationLng) {
                alert("Please select both locations.");
                return;
            }

            fetch(`https://router.project-osrm.org/route/v1/driving/${pickupLng},${pickupLat};${destinationLng},${destinationLat}?overview=full&geometries=geojson`)
                .then(response => response.json())
                .then(data => {
                    if (data.code !== "Ok") return;

                    // Clear previous elements
                    if (routeLayer) map.removeLayer(routeLayer);
                    if (pickupMarker) map.removeLayer(pickupMarker);
                    if (destinationMarker) map.removeLayer(destinationMarker);

                    // Create solid blue route line (removed dashArray)
                    routeLayer = L.polyline(
                        data.routes[0].geometry.coordinates.map(coord => [coord[1], coord[0]]), 
                        {
                            color: '#3366ff', // Bright blue color
                            weight: 6,        // Thicker line
                            opacity: 1,       // Fully opaque
                            lineJoin: 'round' // Smooth corners
                        }
                    ).addTo(map);

                    // Add markers
                    pickupMarker = L.marker([pickupLat, pickupLng], {
                        icon: L.divIcon({
                            className: 'start-marker',
                            html: '<div style="background:#2ecc71;width:24px;height:24px;border-radius:50%;border:3px solid white"></div>',
                            iconSize: [30, 30]
                        })
                    }).addTo(map).bindPopup("Pickup");

                    destinationMarker = L.marker([destinationLat, destinationLng], {
                        icon: L.divIcon({
                            className: 'end-marker',
                            html: '<div style="background:#e74c3c;width:24px;height:24px;border-radius:50%;border:3px solid white"></div>',
                            iconSize: [30, 30]
                        })
                    }).addTo(map).bindPopup("Destination");

                    // Fit view to route
                    map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });

                    // Update price display
                    const distanceKm = (data.routes[0].distance / 1000).toFixed(2);
                    const price = calculatePrice(distanceKm);
                    updatePriceDisplay(distanceKm, price);
                });
        }, 300);
    }

    function calculatePrice(distanceKm) {
        let price = 10;
        if (distanceKm > 1) price += (Math.ceil(distanceKm) - 1) * 12;
        return price.toFixed(2);
    }

    function updatePriceDisplay(distance, price) {
        document.getElementById("ride_price").value = price;
        
        let display = document.getElementById("priceDisplay");
        if (!display) {
            display = document.createElement("div");
            display.id = "priceDisplay";
            display.style.marginTop = "15px";
            display.style.fontSize = "18px";
            document.querySelector(".card").appendChild(display);
        }
        display.innerHTML = `
            <div style="background:#2c3e50;padding:10px;border-radius:5px;color:white;">
                <p style="margin:0;">Distance: <strong>${distance} km</strong></p>
                <p style="margin:0;">Price: <strong style="color:#0FFF50;">₱${price}</strong></p>
            </div>
        `;
        
        document.getElementById("requestRideBtn").style.display = "block";
    }
</script>

</body>
</html>


