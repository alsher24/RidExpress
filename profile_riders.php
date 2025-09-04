<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rider Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #1e2a38;
            color: #ffffff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .profile-container {
            background: rgba(44, 57, 75, 0.95);
            border-radius: 15px;
            padding: 30px;
            width: 350px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            text-align: center;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 3px solid #0bd046;
        }

        .rider-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .rider-id {
            font-size: 14px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .rider-info {
            font-size: 15px;
            margin-bottom: 10px;
        }

        .logout-btn {
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #ff4f4f;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .logout-btn:hover {
            background-color: #e33434;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <img src="https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png" alt="Rider Profile" class="profile-image">
        <div class="rider-name">John Doe</div>
        <div class="rider-id">Rider ID: RDR12345</div>
        <div class="rider-info">Email: johndoe@example.com</div>
        <div class="rider-info">Phone: +1 (555) 123-4567</div>
        <div class="rider-info">Status: <span style="color: #0bd046; font-weight: bold;">Active</span></div>

        <a href="logout.php">
            <button class="logout-btn">Logout</button>
        </a>
        <a href="upload_license.php">
            <button class="logout-btn">Upload ID</button>
        </a>
    </div>

</body>
</html>
