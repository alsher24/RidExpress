<?php
session_start();
require_once 'db_connection.php';
include 'admin_bar.php'; 

// Fetch unique conversations (latest open complaint per user/rider)
$sql = "SELECT cc1.* 
        FROM chat_complaints cc1
        INNER JOIN (
            SELECT user_id, sender_type, MAX(created_at) as latest
            FROM chat_complaints
            WHERE status = 'open'
            GROUP BY user_id, sender_type
        ) cc2 ON cc1.user_id = cc2.user_id 
               AND cc1.sender_type = cc2.sender_type
               AND cc1.created_at = cc2.latest
        ORDER BY cc1.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$conversations = $stmt->fetchAll();

// Get user/rider details
function getUserDetails($pdo, $user_id, $sender_type) {
    if ($sender_type == 'user') {
        $sql = "SELECT id, full_name as name, email, phone_number FROM users WHERE id = ?";
    } else {
        $sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name, email, contact_number as phone_number FROM riders WHERE id = ?";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Get initials from name
function getInitials($name) {
    $names = explode(' ', $name);
    $initials = '';
    foreach ($names as $n) {
        $initials .= strtoupper(substr($n, 0, 1));
        if (strlen($initials) >= 2) break;
    }
    return $initials;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Complaints</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    background-color: #121212;
    color: #f5f5f5;
    font-family: 'Arial', sans-serif;
    margin-left: 240px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

h1 {
    color: #ecf0f1;
}

.conversation-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
    width: 100%;
    max-width: 100%; /* Ensure it takes up the entire width of the container */
    overflow-x: auto; /* Allow horizontal scrolling if needed */
}

.conversation {
    background-color: #34495e;
    padding: 15px;
    border-radius: 10px;
    display: flex;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    width: 100%; /* Make sure the conversation spans the full width */
    max-width: 100%; /* Prevent overflow */
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #6C63FF;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.user-details {
    line-height: 1.4;
}

.user-name {
    font-weight: 500;
}

.message {
    flex-grow: 1;
    background-color: #2c3e50;
    border-radius: 10px;
    padding: 10px 15px;
    color: #f5f5f5;
    margin-left: 10px;
    word-wrap: break-word;
}

.status {
    font-weight: bold;
}

.status-open {
    color: #e74c3c;
}

.status-resolved {
    color: #2ecc71;
}

.view-btn {
    background-color: #6C63FF;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
}

.view-btn:hover {
    background-color: #5a53e5;
}

@media (max-width: 768px) {
    .conversation {
        flex-direction: column;
    }

    .user-info {
        flex-direction: column;
        align-items: flex-start;
    }

    .message {
        margin-left: 0;
        margin-top: 10px;
    }
}

/* Responsive adjustments to ensure the layout stretches across the screen */
@media (min-width: 1024px) {
    .conversation-container {
        width: 80%; /* This allows the container to take up 80% of the screen width */
        max-width: 100%;
    }

    .conversation {
        max-width: 100%; /* Ensure conversation block can stretch to the container's width */
    }
}

    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Customer Support</h1>

        <?php if (empty($conversations)): ?>
            <p class="alert alert-info">No open conversations found.</p>
        <?php else: ?>
            <div class="conversation-container">
                <?php foreach ($conversations as $conversation): 
                    $user = getUserDetails($pdo, $conversation['user_id'], $conversation['sender_type']);
                    $initials = getInitials($user['name'] ?? '');
                    $status_class = 'status-' . $conversation['status'];
                ?>
                    <div class="conversation">
                        <div class="user-info">
                            <div class="user-avatar"><?= $initials ?></div>
                            <div class="user-details">
                                <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></div>
                                <div class="user-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="message">
                            <?= htmlspecialchars(substr($conversation['message'], 0, 150)) ?><?= strlen($conversation['message']) > 150 ? '...' : '' ?>
                        </div>
                        <div class="status <?= $status_class ?>">
                            <?= ucfirst($conversation['status']) ?>
                        </div>
                        <a href="view_complaint.php?id=<?= $user['id'] ?>" class="view-btn">View Message</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
