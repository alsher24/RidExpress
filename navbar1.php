<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideXpress</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

    <style>
        * {
  margin: 0 auto;
  font-family: 'Open Sans', sans-serif;
}

.nav-link {
  background: none !important;
  color: #2c394b !important;
  font-weight: bolder;
  border: none !important;
}

.nav-item{
  border: none !important;
}


button {
    border: none !important;
    outline: none !important;
}

.tab-content {
    min-height: 85vh;
    padding-bottom: 80px; /* Prevents content from overlapping with the navbar */
    -ms-overflow-style: none; /* Hide scrollbar for IE/Edge */
    scrollbar-width: none; /* Hide scrollbar for Firefox */
}
.tab-content::-webkit-scrollbar {
    display: none; /* Hide scrollbar for Chrome/Safari */
}

/* Icons */
.bi {
    color: #dfd3fa;
    font-size: 1.7em;
}

.bi:hover {
    color: #9287ac;
    font-size: 1.7em;
}

 /* Styling the floating headphone icon */
 .floating-icon {
            position: fixed;
            bottom: 100px;
            right: 20px;
            background-color: #0f071f;
            color: #dfd3fa;
            font-size: 2rem;
            border-radius: 50%;
            padding: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        /* Styling the floating button */
.floating-button {
    position: fixed;
    bottom: 210px;
    right: 20px;
    background-color: #0f071f;
    color: #dfd3fa;
    font-size: 2rem;
    border-radius: 60%;
    padding: 15px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    transition: background-color 0.3s ease;
}

    </style>
</head>

<body>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab"
            style="background-color: #f0f0f0 !important;">
            <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                    </div>
                    <div class="carousel-item">
                    </div>
                    <div class="carousel-item">
                    </div>
                </div>
            </div>
        </div>

        <!-- Halaman Pengenalan atau Intro -->
        <div class="tab-pane fade pt-1 pb-4" id="notes" role="tabpanel" aria-labelledby="notes-tab"
            style="background-color: #2c394b;">
        </div>

        <!-- Halaman Blog -->
        <div class="tab-pane fade pt-1 pb-4" id="project" 
        role="tabpanel" aria-labelledby="project-tab" style="background-color: #2c394b;">
        </div>

        <!-- Halaman Notifikasi -->
        <div class="tab-pane fade pt-1 pb-4" id="collection" role="tabpanel" aria-labelledby="collection-tab" style="background-color: #2c394b;">
            
    </div>

    <nav class="navbar navbar-light navbar-expand rounded-pill mb-3 ms-3 me-3 fixed-bottom shadow"
    style="background: #0f071f;">

        <ul class="nav nav-justified w-100" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button"
                    role="tab" aria-controls="home" aria-selected="true">
                    <a href="dashboard1.php" style="text-decoration: none; color: inherit;">
    <span><i class="bi bi-house-fill"></i></span>
</a>

                </button>
            </li>
            <li class="nav-item" role="presentation">
    <a class="nav-link" id="notes-tab" href="rider_ride_requests.php" role="tab" aria-controls="intro" aria-selected="true">
        <span><i class="bi bi-journal-album"></i></span>
    </a>
</li>

            
            <li class="nav-item" role="presentation">
    <a class="nav-link" id="project-tab" href="rider_bookings.php" role="tab" aria-controls="profile" aria-selected="false">
        <span><i class="bi bi-pc-display"></i></span>
    </a>
</li>


            <li class="nav-item" role="presentation">
                <button class="nav-link" id="collection-tab" data-bs-toggle="tab" data-bs-target="#collection" type="button"
                    role="tab" aria-controls="notif" aria-selected="false">
                    <span><i class="bi bi-collection"></i></span>
                </button>
            </li>
            </li>
        </ul>
    </nav>

    
    <a href="profile_riders.php">
    <div class="floating-button">
        <i class="bi bi-person"></i>
    </div>
</a>



    <a href="customer_service.php">
        <div class="floating-icon">
            <i class="bi bi-headphones"></i>
        </div>
    </a>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>

</body>

</html>