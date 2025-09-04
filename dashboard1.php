<?php
session_start();
include 'db.php';
include 'navbar1.php';

$rider_id = $_SESSION['user_id']; // Adjust if your session key differs

$checkQuery = "SELECT is_checked FROM riders WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$stmt->bind_result($is_checked);
$stmt->fetch();
$stmt->close();

// Function to calculate distance (Haversine Formula)
function haversine($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius of the Earth in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c; // Distance in km
}

// Fetch all ride requests with pending status including price
$sql = "SELECT r.id, u.full_name, r.pickup_location, r.destination_location, 
               r.pickup_lat, r.pickup_lng, r.destination_lat, r.destination_lng, r.price,
               r.created_at, u.phone_number
        FROM ride_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.status='pending' 
        ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard - Ride Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>

        
        :root {
            --primary-bg: #0f071f;
            --secondary-bg: #2c394b;
            --accent-color: #0FFF50;
            --text-color: #dfd3fa;
            --hover-bg: #384c65;
            --warning-color: #FFC107;
            --danger-color: #DC3545;
            --info-color: #17A2B8;
        }
        
        body {
            background-color: var(--primary-bg);
            color: var(--text-color);
            font-family: 'Open Sans', sans-serif;
        }
        
        .container {
            margin-top: 50px;
            padding-bottom: 30px;
        }
        
        .card {
            background-color: var(--secondary-bg);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.4);
            background-color: var(--hover-bg);
        }
        
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .price-badge {
            background-color: var(--primary-bg);
            color: var(--accent-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            border: 2px solid var(--accent-color);
        }
        
        .distance-badge {
            background-color: var(--primary-bg);
            color: var(--info-color);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 14px;
            display: inline-block;
            margin-left: 10px;
            border: 1px solid var(--info-color);
        }
        
        .time-badge {
            background-color: var(--primary-bg);
            color: var(--warning-color);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 14px;
            display: inline-block;
            margin-left: 10px;
            border: 1px solid var(--warning-color);
        }
        
        .btn-accept {
            background-color: var(--accent-color);
            color: #000;
            font-weight: bold;
            border-radius: 10px;
            padding: 10px 20px;
            margin: 5px;
        }
        
        .btn-view {
            background-color: var(--info-color);
            color: #fff;
            font-weight: bold;
            border-radius: 10px;
            padding: 10px 20px;
            margin: 5px;
        }
        
        .btn-close {
            background-color: var(--danger-color);
            color: #fff;
            font-weight: bold;
            border-radius: 10px;
            padding: 10px 20px;
            margin: 5px;
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
            z-index: 1050;
        }
        
        #mapContainer {
            width: 95%;
            height: 95%;
            background: white;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }
        
        #map {
            width: 100%;
            height: 100%;
        }
        
        #closeMap {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            z-index: 1001;
        }
        
        .rider-info {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 15px;
            border-radius: 10px;
            z-index: 1000;
            color: white;
            max-width: 300px;
        }
        
        .customer-info {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 15px;
            border-radius: 10px;
            z-index: 1000;
            color: white;
            max-width: 300px;
        }
        
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1100;
        }
        
        .no-requests {
            text-align: center;
            padding: 40px;
            background-color: var(--secondary-bg);
            border-radius: 15px;
        }
        
        .highlight-text {
            color: var(--accent-color);
            font-weight: bold;
        }
        
        .location-icon {
            color: var(--accent-color);
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .card {
                padding: 15px;
            }
            
            #mapContainer {
                width: 100%;
                height: 100%;
                border-radius: 0;
            }
        }

        /* Notification Popup Styles */
    .notification-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #2c394b;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        z-index: 9999;
        width: 90%;
        max-width: 500px;
        border: 2px solid #ffc107;
        display: block;
        animation: slideIn 0.5s ease-out;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translate(-50%, -60%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }
    
    .notification-content {
        padding: 20px;
        color: #dfd3fa;
    }
    
    .notification-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #3a3a3a;
        padding-bottom: 10px;
    }
    
    .notification-header h3 {
        margin: 0;
        flex: 1;
        color: #ffc107;
        font-size: 1.3rem;
    }
    
    .notification-header i {
        font-size: 1.5rem;
        color: #ffc107;
        margin-right: 15px;
    }
    
    .close-notification {
        background: none;
        border: none;
        color: #dfd3fa;
        font-size: 1.2rem;
        cursor: pointer;
        transition: color 0.3s;
    }
    
    .close-notification:hover {
        color: #ffc107;
    }
    
    .notification-body {
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .notification-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .btn-notification {
        padding: 8px 15px;
        border-radius: 8px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }
    
    .btn-notification i {
        font-size: 0.9rem;
    }
    
    .btn-notification:not(.btn-close) {
        background-color: #6C63FF;
        color: white;
    }
    
    .btn-notification:not(.btn-close):hover {
        background-color: #4D44DB;
    }
    
    .btn-close {
        background-color: #3a3a3a;
        color: #dfd3fa;
    }
    
    .btn-close:hover {
        background-color: #4a4a4a;
    }
    
    /* Maintenance Banner Styles */
    .maintenance-banner {
        position: sticky;
        top: 0;
        left: 0;
        width: 100%;
        background-color: #ffc107;
        color: #000;
        padding: 12px 20px;
        text-align: center;
        font-weight: bold;
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        animation: slideDown 0.5s ease-out;
    }
    
    @keyframes slideDown {
        from { transform: translateY(-100%); }
        to { transform: translateY(0); }
    }
    
    .maintenance-banner i {
        font-size: 1.2rem;
    }

      .disabled-card {
            pointer-events: none;
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Modify existing notification-footer button */
        .notification-footer .btn-close {
            background-color: var(--danger-color);
        }

        .rounded-circle {
        border: 2px solid var(--accent-color);
        object-fit: cover;
    }
    
    /* For the customer info in the map modal */
    .customer-info img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 10px;
        border: 2px solid var(--accent-color);
    }

    .income-green {
    color: #0FFF50;
}
    </style>
</head>
<body>

<?php if ($is_checked == 0): ?>
    <div id='maintenanceNotification' class='notification-popup'>
        <div class='notification-content'>
            <div class='notification-header'>
                <i class='fas fa-exclamation-triangle'></i>
                <h3>Vehicle Maintenance Required</h3>
                <button class='close-notification' onclick="window.location.href='logout.php'">
                    <i class='fas fa-times'></i>
                </button>
            </div>
            <div class='notification-body'>
                <p>Your vehicle requires maintenance. Please visit RideExpress Company for a vehicle check before accepting any rides.</p>
            </div>
            <div class='notification-footer'>
                
                <button class='btn-notification ' onclick="window.location.href='logout.php';">
                    <i class='fas fa-calendar-alt'></i>Dismiss & Logout
                </button>
                <button class='btn-notification btn-close" onclick="window.location.href='logout.php">
                    <i class='fas fa-times'></i>Schedule Maintenance 
                </button>
            </div>
        </div>
    </div>
    
    <div class='maintenance-banner'>
        <span>⚠ Vehicle maintenance required - Please visit RideExpress Company</span>
    </div>
<?php endif; ?>
<div class="container">

<div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-week"></i> Weekly Income</h5>
                    <span class="time-badge">
                        <?php 
                        $startOfWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
                        $endOfWeek = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                        echo date('M j', strtotime($startOfWeek)).' - '.date('M j, Y', strtotime($endOfWeek));
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php
                    // Query to get weekly completed rides income
                    $incomeQuery = "SELECT SUM(price) as weekly_income 
                                    FROM ride_requests 
                                    WHERE rider_id = ? 
                                    AND status = 'completed' 
                                    AND created_at BETWEEN ? AND ?";
                    $stmt = $conn->prepare($incomeQuery);
                    $stmt->bind_param("iss", $rider_id, $startOfWeek, $endOfWeek);
                    $stmt->execute();
                    $resultIncome = $stmt->get_result();
                    $incomeData = $resultIncome->fetch_assoc();
                    $weeklyIncome = $incomeData['weekly_income'] ?? 0;
                    $stmt->close();
                    ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                        <h2 class="mb-0 income-green"><i class="fas fa-peso-sign"></i> <?= number_format($weeklyIncome, 2) ?></h2>
                            <p class="mb-0 text-muted">Total earnings this week</p>
                        </div>
                        <div class="text-end">
                            <?php
                            // Count number of completed rides this week
                            $countQuery = "SELECT COUNT(*) as ride_count 
                                          FROM ride_requests 
                                          WHERE rider_id = ? 
                                          AND status = 'completed' 
                                          AND created_at BETWEEN ? AND ?";
                            $stmt = $conn->prepare($countQuery);
                            $stmt->bind_param("iss", $rider_id, $startOfWeek, $endOfWeek);
                            $stmt->execute();
                            $resultCount = $stmt->get_result();
                            $countData = $resultCount->fetch_assoc();
                            $rideCount = $countData['ride_count'] ?? 0;
                            $stmt->close();
                            ?>
                            <span class="badge bg-primary rounded-pill">
                                <i class="fas fa-taxi"></i> <?= $rideCount ?> rides
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="text-center mb-4">Available Ride Requests</h2>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $distanceKm = haversine($row['pickup_lat'], $row['pickup_lng'], $row['destination_lat'], $row['destination_lng']);
                $distanceFormatted = number_format($distanceKm, 1);
                
                // Use database price if available, otherwise calculate
                $price = isset($row['price']) ? $row['price'] : (10 + (ceil($distanceKm) - 1) * 12);
                $priceFormatted = number_format($price, 2);
                
                // Calculate estimated time (assuming 30km/h average speed)
                $estimatedTime = ceil(($distanceKm / 30) * 60);
            ?>
            
         <div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100 <?= $is_checked == 0 ? 'disabled-card' : '' ?>" 
         onclick="<?= $is_checked == 0 ? '' : "showRideDetails(
            '".htmlspecialchars($row['id'])."',
            '".htmlspecialchars($row['full_name'])."',
            '".htmlspecialchars($row['pickup_location'])."',
            '".htmlspecialchars($row['destination_location'])."',
            '".$row['pickup_lat']."',
            '".$row['pickup_lng']."',
            '".$row['destination_lat']."',
            '".$row['destination_lng']."',
            '".$priceFormatted."',
            '".htmlspecialchars($row['phone_number'])."',
            '".date('M j, g:i A', strtotime($row['created_at']))."'
         )" ?>">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- Default profile picture -->
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRv12rpCJwVnia-jaZ-v1WN3UGCeaxM63wjCg&s" 
                     class="rounded-circle me-2" 
                     width="40" 
                     height="40" 
                     alt="User profile">
                <h5 class="mb-0"><?= htmlspecialchars($row['full_name']) ?></h5>
            </div>
            <span class="time-badge">
                <i class="fas fa-clock"></i> <?= $estimatedTime ?> min
            </span>
        </div>
        
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="price-badge">
                                <i class="fas fa-peso-sign"></i> <?= $priceFormatted ?>
                            </span>
                            <span class="distance-badge">
                                <i class="fas fa-route"></i> <?= $distanceFormatted ?> km
                            </span>
                        </div>
                        
                        <div class="location-info mb-2">
                            <p class="mb-1">
                                <i class="fas fa-map-marker-alt location-icon"></i>
                                <strong>From:</strong> <?= htmlspecialchars($row['pickup_location']) ?>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-flag-checkered location-icon"></i>
                                <strong>To:</strong> <?= htmlspecialchars($row['destination_location']) ?>
                            </p>
                        </div>
                        
                        <div class="text-muted small mt-2">
                            <i class="fas fa-calendar-alt"></i> <?= date('M j, g:i A', strtotime($row['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-requests">
            <i class="fas fa-taxi fa-4x mb-3" style="color: var(--accent-color);"></i>
            <h3>No Ride Requests Available</h3>
            <p class="lead">There are currently no pending ride requests.</p>
            <p>Check back later or refresh the page.</p>
            <button class="btn btn-accept" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Ride Details Modal -->
<div id="rideModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--secondary-bg);">
            <div class="modal-header border-0">
                <div class="d-flex align-items-center">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRv12rpCJwVnia-jaZ-v1WN3UGCeaxM63wjCg&s" 
                         class="rounded-circle me-2" 
                         width="40" 
                         height="40" 
                         alt="User profile">
                    <h5 class="modal-title" id="modalName"></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="color: white;"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <p><i class="fas fa-map-marker-alt location-icon"></i> <strong>Pick-up:</strong></p>
                        <p id="modalPickup" class="ps-4"></p>
                    </div>
                    <div class="col-6">
                        <p><i class="fas fa-flag-checkered location-icon"></i> <strong>Destination:</strong></p>
                        <p id="modalDestination" class="ps-4"></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <p><i class="fas fa-phone location-icon"></i> <strong>Phone: 0960909035</strong></p>
                        <p id="modalPhone" class="ps-4"></p>
                    </div>
                    <div class="col-6">
                        <p><i class="fas fa-clock location-icon"></i> <strong>Request Time:</strong></p>
                        <p id="modalTime" class="ps-4"></p>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="price-badge">
                        <i class="fas fa-peso-sign"></i> <span id="modalPrice"></span>
                    </span>
                    <span class="distance-badge">
                        <i class="fas fa-route"></i> <span id="modalDistance"></span> km
                    </span>
                    <span class="time-badge">
                        <i class="fas fa-clock"></i> <span id="modalEstTime"></span> min
                    </span>
                </div>
                
                <input type="hidden" id="modalRideId">
            </div>
            <div class="modal-footer border-0 d-flex justify-content-center">
                <button type="button" class="btn btn-view" onclick="viewRoute()">
                    <i class="fas fa-map-marked-alt"></i> View Route
                </button>
                <button type="button" class="btn btn-accept" onclick="acceptRide()">
                    <i class="fas fa-check-circle"></i> Accept Ride
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Full-Screen Map Modal -->
<div id="mapModal">
    <div id="mapContainer">
        <button id="closeMap" onclick="closeMap()">
            <i class="fas fa-times"></i> Close
        </button>
        <div id="map"></div>
        <div class="rider-info">
            <h5><i class="fas fa-motorcycle"></i> Your Location</h5>
            <p id="riderLocationText">Getting your location...</p>
            <p id="distanceToPickup">Calculating distance to pickup...</p>
            <p id="etaToPickup">Calculating ETA...</p>
        </div>
        <div class="customer-info">
    <div class="d-flex align-items-center">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRv12rpCJwVnia-jaZ-v1WN3UGCeaxM63wjCg&s" 
             alt="Customer profile">
        <div>
            <h5><i class="fas fa-user"></i> Customer Info</h5>
            <p id="customerName"></p>
            <p id="customerPhone"></p>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="loading-spinner" id="loadingSpinner">
    <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

function showRideDetails(...args) {
        <?php if ($is_checked == 0): ?>
            alert('Vehicle maintenance required. You will be logged out.');
            window.location.href = 'logout.php';
            return;
        <?php endif; ?>
        // ... [rest of the original function]
    }

    // Modify dismiss button handler
    document.querySelectorAll('.btn-close').forEach(button => {
        button.addEventListener('click', function() {
            window.location.href = 'logout.php';
        });
    });

    // Global variables
    let pickupLat, pickupLng, destinationLat, destinationLng;
    let map, routeLayer, riderMarker, riderLat, riderLng;
    let currentRideId, currentCustomerName, currentCustomerPhone;
    let modal = new bootstrap.Modal(document.getElementById('rideModal'));
    
    // Custom icons
    const riderIcon = L.divIcon({
        className: 'custom-rider-icon',
        html: '<i class="fas fa-motorcycle fa-2x" style="color: #0FFF50;"></i>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
    
    const pickupIcon = L.divIcon({
        className: 'custom-pickup-icon',
        html: '<i class="fas fa-map-marker-alt fa-2x" style="color: #FFC107;"></i>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });
    
    const destinationIcon = L.divIcon({
        className: 'custom-destination-icon',
        html: '<i class="fas fa-flag-checkered fa-2x" style="color: #DC3545;"></i>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    // Show ride details in modal
    function showRideDetails(id, name, pickup, destination, pLat, pLng, dLat, dLng, price, phone, time) {
        currentRideId = id;
        currentCustomerName = name;
        currentCustomerPhone = phone;
        
        document.getElementById("modalName").innerText = name;
        document.getElementById("modalPickup").innerText = pickup;
        document.getElementById("modalDestination").innerText = destination;
        document.getElementById("modalPrice").innerText = price;
        document.getElementById("modalPhone").innerText = phone;
        document.getElementById("modalTime").innerText = time;
        document.getElementById("modalRideId").value = id;
        
        // Calculate and display distance and estimated time
        const distanceKm = calculateDistance(pLat, pLng, dLat, dLng);
        document.getElementById("modalDistance").innerText = distanceKm.toFixed(1);
        
        const estTime = Math.ceil((distanceKm / 30) * 60); // 30km/h average speed
        document.getElementById("modalEstTime").innerText = estTime;
        
        pickupLat = parseFloat(pLat);
        pickupLng = parseFloat(pLng);
        destinationLat = parseFloat(dLat);
        destinationLng = parseFloat(dLng);
        
        modal.show();
    }

    // Accept ride function
    function acceptRide() {
    showLoading(true);
    
    fetch('accept_ride.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ride_id=${currentRideId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        showLoading(false);
        if (data.success) {
            modal.hide();
            window.location.href = `tracking.php?ride_id=${currentRideId}`;
        } else {
            alert('Error: ' + (data.message || 'Failed to accept ride'));
        }
    })
    .catch(error => {
        showLoading(false);
        console.error('Error:', error);
        alert('Failed to accept ride. Please check your connection and try again.');
    });
}

    // View route on map
    function viewRoute() {
        modal.hide();
        document.getElementById("mapModal").style.display = "flex";
        
        // Update customer info on map
        document.getElementById("customerName").innerHTML = `<i class="fas fa-user"></i> <strong>${currentCustomerName}</strong>`;
        document.getElementById("customerPhone").innerHTML = `<i class="fas fa-phone"></i> ${currentCustomerPhone}`;
        
        initMap();
    }

    // Initialize map
    function initMap() {
        if (!map) {
            map = L.map('map').setView([pickupLat, pickupLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
        } else {
            map.setView([pickupLat, pickupLng], 13);
        }
        
        // Clear existing layers
        if (routeLayer) {
            map.removeLayer(routeLayer);
        }
        
        // Add pickup and destination markers
        L.marker([pickupLat, pickupLng], { icon: pickupIcon })
            .addTo(map)
            .bindPopup("<b>Pick-up Location</b>")
            .openPopup();
            
        L.marker([destinationLat, destinationLng], { icon: destinationIcon })
            .addTo(map)
            .bindPopup("<b>Destination</b>");
        
        // Start tracking rider location
        trackRiderLocation();
    }

    // Track rider's location
    function trackRiderLocation() {
        if (!navigator.geolocation) {
            alert("Geolocation is not supported by your browser.");
            return;
        }

        const successCallback = (position) => {
            riderLat = position.coords.latitude;
            riderLng = position.coords.longitude;
            
            // Update rider marker
            if (riderMarker) {
                riderMarker.setLatLng([riderLat, riderLng]);
            } else {
                riderMarker = L.marker([riderLat, riderLng], { 
                    icon: riderIcon,
                    zIndexOffset: 1000
                }).addTo(map).bindPopup("<b>Your Location</b>");
            }
            
            // Update rider info box
            document.getElementById("riderLocationText").innerHTML = 
                `Lat: ${riderLat.toFixed(6)}, Lng: ${riderLng.toFixed(6)}`;
            
            // Calculate distance to pickup
            const distanceToPickup = calculateDistance(riderLat, riderLng, pickupLat, pickupLng);
            document.getElementById("distanceToPickup").innerHTML = 
                `<i class="fas fa-route"></i> Distance to pickup: ${distanceToPickup.toFixed(1)} km`;
            
            // Calculate ETA (assuming 30km/h average speed in city traffic)
            const etaMinutes = Math.ceil((distanceToPickup / 30) * 60);
            document.getElementById("etaToPickup").innerHTML = 
                `<i class="fas fa-clock"></i> ETA: ~${etaMinutes} min`;
            
            // Fetch and display route from rider to pickup
            fetchRoute(riderLat, riderLng, pickupLat, pickupLng, 'blue');
            
            // Fetch and display route from pickup to destination (if not already shown)
            if (!document.querySelector('.destination-route')) {
                fetchRoute(pickupLat, pickupLng, destinationLat, destinationLng, 'green', 'destination-route');
            }
        };

        const errorCallback = (error) => {
            console.error("Error getting location:", error);
            document.getElementById("riderLocationText").textContent = 
                "Error getting your location. Please ensure location services are enabled.";
        };

        const options = {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 5000
        };

        // Get initial position and then watch for changes
        navigator.geolocation.getCurrentPosition(successCallback, errorCallback, options);
        navigator.geolocation.watchPosition(successCallback, errorCallback, options);
    }

    // Fetch route from OSRM
    function fetchRoute(startLat, startLng, endLat, endLng, color = 'blue', className = '') {
        const routeUrl = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
        
        fetch(routeUrl)
            .then(response => response.json())
            .then(data => {
                if (data.routes && data.routes.length > 0) {
                    const routeCoords = data.routes[0].geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    
                    // Remove existing route layer if it exists
                    if (routeLayer) {
                        map.removeLayer(routeLayer);
                    }
                    
                    // Add new route layer
                    routeLayer = L.polyline(routeCoords, { 
                        color: color,
                        weight: 5,
                        className: className
                    }).addTo(map);
                    
                    // Fit map to show both rider and pickup locations
                    map.fitBounds([
                        [riderLat, riderLng],
                        [pickupLat, pickupLng],
                        [destinationLat, destinationLng]
                    ], { padding: [50, 50] });
                }
            })
            .catch(error => console.error("Error fetching route:", error));
    }

    // Calculate distance between two coordinates
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Close map modal
    function closeMap() {
        document.getElementById("mapModal").style.display = "none";
    }

    // Show/hide loading spinner
    function showLoading(show) {
        document.getElementById('loadingSpinner').style.display = show ? 'block' : 'none';
    }

    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event to cards (for mobile touch)
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', function() {
                // The onclick handler on the card will handle this
            });
        });
    });
</script>
</body>
</html>