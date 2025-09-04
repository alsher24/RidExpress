<?php
session_start();
require_once 'db_connection.php';



// Get the user_id from the URL
if (!isset($_GET['id'])) {
    die("User ID is missing.");
}

$user_id = $_GET['id'];

// Fetch user details (assuming you have a users table)
// First try to get from users table
$user_sql = "SELECT id, full_name as name, email FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// If not found in users table, try riders table
if (!$user) {
    $rider_sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name, email FROM riders WHERE id = ?";
    $rider_stmt = $pdo->prepare($rider_sql);
    $rider_stmt->execute([$user_id]);
    $user = $rider_stmt->fetch();
    
    if (!$user) {
        die("User not found in either table.");
    }
}
// Fetch all messages related to the user
$sql = "SELECT * FROM chat_complaints WHERE user_id = ? ORDER BY created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

// Handle message submission from the admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $sql = "INSERT INTO chat_complaints (user_id, message, sender_type, status) VALUES (?, ?, 'admin', 'open')";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$user_id, $message])) {
            // Return JSON for AJAX requests
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode([
                    'status' => 'success',
                    'message' => $message,
                    'sender_type' => 'admin',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                exit;
            } else {
                header("Location: view_complaint.php?id=$user_id");
                exit;
            }
        } else {
            echo "Failed to send message.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Complaint Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
    :root {
        --primary-color: #6C63FF;
        --secondary-color: #4D44DB;
        --dark-bg: #121212;
        --card-bg: #2c3e50;
        --border-color: #3d5166;
        --text-primary: #dfd3fa;
        --text-secondary: #a0a8c0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: var(--dark-bg);
        color: var(--text-primary);
    }

    .admin-chat-container {
        display: flex;
        height: 100vh;
        max-width: 1200px;
        margin: 0 auto;
        background-color: var(--dark-bg);
    }

    .complaints-sidebar {
        width: 350px;
        background-color: var(--card-bg);
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .sidebar-header h2 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .search-bar {
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .search-bar input {
        width: 100%;
        padding: 10px 15px;
        background-color: #34495e;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        color: var(--text-primary);
        outline: none;
        font-size: 14px;
    }

    .complaints-list {
        flex: 1;
        overflow-y: auto;
    }

    .complaint-item {
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        cursor: pointer;
        transition: all 0.2s;
    }

    .complaint-item:hover {
        background-color: #34495e;
    }

    .complaint-item.active {
        background-color: #3d5166;
        border-left: 3px solid var(--primary-color);
    }

    .complaint-item h3 {
        font-size: 15px;
        margin-bottom: 5px;
        color: var(--text-primary);
    }

    .complaint-item p {
        font-size: 13px;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .complaint-meta {
        display: flex;
        justify-content: space-between;
        margin-top: 5px;
        font-size: 12px;
    }

    .status-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }

    .status-open {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }

    .status-resolved {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
    }

    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: var(--card-bg);
    }

    .chat-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chat-header-info {
        display: flex;
        align-items: center;
    }

    .chat-header-info img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 15px;
        object-fit: cover;
        border: 2px solid var(--primary-color);
    }

    .chat-header-info h2 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .chat-header-info p {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .chat-header-actions {
        display: flex;
        gap: 15px;
    }

    .chat-header-actions button {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 18px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chat-header-actions button:hover {
        color: var(--primary-color);
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: #34495e;
        background-image: linear-gradient(rgba(255,255,255,0.02) 50%, transparent 50%);
    }

    .message {
        max-width: 70%;
        margin-bottom: 15px;
        padding: 12px 16px;
        border-radius: 18px;
        position: relative;
        word-wrap: break-word;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message.admin {
        background-color: var(--primary-color);
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }

    .message.user {
        background-color: #3d5166;
        color: var(--text-primary);
        margin-right: auto;
        border-bottom-left-radius: 4px;
    }

    .message-time {
        font-size: 11px;
        margin-top: 5px;
        opacity: 0.8;
        text-align: right;
        color: var(--text-secondary);
    }

    .chat-input-area {
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: var(--card-bg);
    }

    .chat-input-area textarea {
        flex: 1;
        padding: 12px 15px;
        background-color: #34495e;
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        border-radius: 24px;
        outline: none;
        font-size: 15px;
        resize: none;
        height: 44px;
        max-height: 120px;
        transition: all 0.2s;
    }

    .chat-input-area textarea:focus {
        border-color: var(--primary-color);
    }

    .send-btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .send-btn:hover {
        background-color: var(--secondary-color);
    }

    .send-btn i {
        font-size: 18px;
    }

    .typing-indicator {
        display: flex;
        margin-bottom: 15px;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .typing-indicator.active {
        opacity: 1;
    }

    .typing-indicator span {
        height: 8px;
        width: 8px;
        margin: 0 2px;
        background-color: var(--text-secondary);
        border-radius: 50%;
        display: inline-block;
        animation: typing 1.5s infinite ease-in-out;
    }

    .resolve-btn {
        background-color: #2ecc71;
        color: white;
        border: none;
        border-radius: 20px;
        padding: 8px 16px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .resolve-btn:hover {
        background-color: #27ae60;
    }

    @media (max-width: 768px) {
        .admin-chat-container {
            flex-direction: column;
            height: auto;
        }
        
        .complaints-sidebar {
            width: 100%;
            height: 50vh;
            border-right: none;
            border-bottom: 1px solid var(--border-color);
        }
        
        .message {
            max-width: 85%;
        }
    }
</style>

</head>
<body>
    <div class="admin-chat-container">
        <div class="complaints-sidebar">
            <div class="sidebar-header">
                <h2>Customer Complaints</h2>
                <button title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            
            <div class="search-bar">
                <input type="text" placeholder="Search complaints...">
            </div>
            
            <div class="complaints-list">
                <!-- Example complaint items - in a real app these would come from the database -->
                <div class="complaint-item <?= $user_id == $user['id'] ? 'active' : '' ?>">
                    <h3><?= htmlspecialchars($user['name'] ?? 'User #' . $user_id) ?></h3>
                    <p><?= !empty($messages) ? htmlspecialchars(substr($messages[count($messages)-1]['message'], 0, 50)) . (strlen($messages[count($messages)-1]['message']) > 50 ? '...' : '') : 'No messages yet' ?></p>
                    <div class="complaint-meta">
                        <span><?= !empty($messages) ? date('M j, g:i a', strtotime($messages[count($messages)-1]['created_at'])) : '' ?></span>
                        <span class="status-badge status-open">Open</span>
                    </div>
                </div>
                
                <!-- Additional complaint items would be listed here -->
            </div>
        </div>
        
        <div class="chat-area">
            <div class="chat-header">
            <div class="chat-header-info">
    <img src="path/to/profile.jpg" alt="User" />
    <div>
    <h2>
    <?php 
    if (isset($user['full_name'])) {
        echo htmlspecialchars($user['full_name']);
    } elseif (isset($user['first_name']) && isset($user['last_name'])) {
        echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
    } else {
        echo 'User #' . htmlspecialchars($user_id);
    }
    ?>
</h2>
    </div>
</div>

                <div class="chat-header-actions">
                    <button title="Mark as resolved" class="resolve-btn" id="resolve-btn">
                        <i class="fas fa-check"></i> Resolve
                    </button>
                    <button title="More options"><i class="fas fa-ellipsis-v"></i></button>
                </div>
            </div>
            
            <div class="chat-messages" id="chat-messages">
                <?php foreach ($messages as $message): ?>
                    <div class="message <?= htmlspecialchars($message['sender_type']) ?>">
                        <p><?= htmlspecialchars($message['message']) ?></p>
                        <div class="message-time">
                            <?= date('g:i a', strtotime($message['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="typing-indicator" id="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            
            <div class="chat-input-area">
                <textarea id="message-input" placeholder="Type your message..." rows="1"></textarea>
                <button class="send-btn" id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Auto-scroll to bottom of chat
            function scrollToBottom() {
                const chatMessages = document.getElementById('chat-messages');
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            scrollToBottom();
            
            // Auto-resize textarea as user types
            $('#message-input').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            
            // Send message on button click or Shift+Enter
            $('#send-btn').click(sendMessage);
            $('#message-input').keypress(function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            function sendMessage() {
                const messageInput = $('#message-input');
                const message = messageInput.val().trim();
                
                if (message !== '') {
                    // Show typing indicator
                    $('#typing-indicator').addClass('active');
                    scrollToBottom();
                    
                    // Disable input while sending
                    messageInput.prop('disabled', true);
                    $('#send-btn').prop('disabled', true);
                    
                    $.ajax({
                        url: window.location.href,
                        method: 'POST',
                        data: { message: message },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                // Add the message to the chat
                                const messageTime = new Date(res.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                const messageHtml = `
                                    <div class="message ${res.sender_type}">
                                        <p>${res.message}</p>
                                        <div class="message-time">${messageTime}</div>
                                    </div>
                                `;
                                $('#chat-messages').append(messageHtml);
                                
                                // Clear and reset input
                                messageInput.val('').height('auto');
                                
                                // Hide typing indicator
                                $('#typing-indicator').removeClass('active');
                                
                                scrollToBottom();
                            } else {
                                alert(res.message);
                            }
                        },
                        error: function() {
                            alert('Error sending message. Please try again.');
                        },
                        complete: function() {
                            // Re-enable input
                            messageInput.prop('disabled', false).focus();
                            $('#send-btn').prop('disabled', false);
                        }
                    });
                }
            }
            
            // Handle resolve button click
            $('#resolve-btn').click(function() {
                if (confirm('Mark this complaint as resolved?')) {
                    // In a real app, you would make an AJAX call to update the status
                    alert('Complaint marked as resolved!');
                    // Then you might redirect or update the UI
                }
            });
            
            // Poll for new messages every 3 seconds
            setInterval(checkForNewMessages, 3000);
            
            function checkForNewMessages() {
                $.ajax({
                    url: 'get_messages.php', // You would need to create this endpoint
                    method: 'GET',
                    data: { user_id: <?= $user_id ?> },
                    success: function(response) {
                        // Compare with existing messages and add new ones
                        // Implementation depends on your backend
                    }
                });
            }
            
            // Auto-focus input on page load
            $('#message-input').focus();
        });
    </script>
</body>
</html>