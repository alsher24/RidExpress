<?php
session_start();
require_once 'db_connection.php';

// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Handle message submission (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_ajax) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'user';
    $message = trim($_POST['message'] ?? '');

    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
        exit;
    }

    try {
        // Determine the correct sender_type based on user_type
        $sender_type = 'user'; // default
        if ($user_type === 'rider') {
            $sender_type = 'rider';
        } elseif ($user_type === 'admin') {
            $sender_type = 'admin';
        }

        $sql = "INSERT INTO chat_complaints (user_id, message, sender_type, status) VALUES (?, ?, ?, 'open')";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$user_id, $message, $sender_type])) {
            $last_id = $pdo->lastInsertId();
            $sql = "SELECT * FROM chat_complaints WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$last_id]);
            $new_message = $stmt->fetch();
            
            echo json_encode([
                'status' => 'success',
                'message' => $new_message['message'],
                'sender_type' => $new_message['sender_type'],
                'created_at' => $new_message['created_at']
            ]);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again.']);
        exit;
    }
}

// Regular page load
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$messages = [];

try {
    $sql = "SELECT * FROM chat_complaints WHERE user_id = ? ORDER BY created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $messages = [];
}
?>

<!-- Your HTML remains the same -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    :root {
        --primary-color: #6C63FF;
        --secondary-color: #4D44DB;
        --dark-bg: #0f071f;
        --card-bg: #2c394b;
        --text-light: #dfd3fa;
        --text-muted: #a0a0a0;
        --border-color: #3a3a3a;
        --user-message-bg: #6C63FF;
        --admin-message-bg: #1a2230;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Open Sans', sans-serif;
    }

    body {
        background-color: var(--dark-bg);
        color: var(--text-light);
    }

    .chat-container {
        display: flex;
        height: 100vh;
        max-width: 1200px;
        margin: 0 auto;
    }

    .sidebar {
        width: 300px;
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
        color: var(--text-light);
    }

    .new-chat-btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .new-chat-btn:hover {
        background-color: var(--secondary-color);
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
    }

    .chat-item {
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        cursor: pointer;
        transition: all 0.2s;
    }

    .chat-item:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .chat-item.active {
        background-color: rgba(108, 99, 255, 0.1);
        border-left: 3px solid var(--primary-color);
    }

    .chat-item h3 {
        font-size: 15px;
        margin-bottom: 5px;
        color: var(--text-light);
    }

    .chat-item p {
        font-size: 13px;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-main {
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
    }

    .chat-header-info h2 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-light);
    }

    .chat-header-info p {
        font-size: 13px;
        color: var(--text-muted);
    }

    .chat-header-actions {
        display: flex;
        gap: 15px;
    }

    .chat-header-actions button {
        background: none;
        border: none;
        color: var(--text-muted);
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
        background-color: var(--dark-bg);
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

    .message.user {
        background-color: var(--user-message-bg);
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }

    .message.admin {
        background-color: var(--admin-message-bg);
        color: var(--text-light);
        margin-right: auto;
        border-bottom-left-radius: 4px;
        border: 1px solid var(--border-color);
    }

    .message-time {
        font-size: 11px;
        margin-top: 5px;
        opacity: 0.8;
        text-align: right;
    }

    .message.admin .message-time {
        color: var(--text-muted);
    }

    .message.user .message-time {
        color: rgba(255, 255, 255, 0.8);
    }

    .chat-input {
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: var(--card-bg);
    }

    .chat-input input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid var(--border-color);
        border-radius: 24px;
        outline: none;
        font-size: 15px;
        transition: all 0.2s;
        background-color: var(--dark-bg);
        color: var(--text-light);
    }

    .chat-input input:focus {
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
        background-color: var(--text-muted);
        border-radius: 50%;
        display: inline-block;
        animation: typing 1.5s infinite ease-in-out;
    }

    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-5px); }
    }

    .no-messages {
        text-align: center;
        color: var(--text-muted);
        padding: 20px;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .chat-container {
            flex-direction: column;
            height: auto;
        }
        
        .sidebar {
            width: 100%;
            height: auto;
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
    <div class="chat-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Support Tickets</h2>
                <button class="new-chat-btn" title="Back to Dashboard" aria-label="Back to Dashboard" onclick="window.location.href='dashboard.php';">
    <i class="fas fa-arrow-left"></i>
</button>

            </div>
            <div class="chat-list">
                <div class="chat-item active">
                    <h3>Current Issue</h3>
                    <p>Active conversation</p>
                </div>
                <!-- Additional chat items would go here -->
            </div>
        </div>
        
        <div class="chat-main">
            <div class="chat-header">
                <div class="chat-header-info">
                    <img src="https://ui-avatars.com/api/?name=Support+Team&background=6C63FF&color=fff" alt="Support Agent">
                    <div>
                        <h2>Customer Support</h2>
                        <p>Typically replies within minutes</p>
                    </div>
                </div>
                <div class="chat-header-actions">
                    <button title="Search"><i class="fas fa-search"></i></button>
                    <button title="More options"><i class="fas fa-ellipsis-v"></i></button>
                </div>
            </div>
            
            <div class="chat-messages" id="chat-messages">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message <?= htmlspecialchars($message['sender_type']) ?>">
                        <p><?= htmlspecialchars($message['message']) ?></p>
                        <div class="message-time">
                            <?= date('h:i A', strtotime($message['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-messages">No messages yet. Start the conversation!</div>
            <?php endif; ?>
            
            <div class="typing-indicator" id="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
            
            <div class="chat-input">
                <input type="text" id="message-input" placeholder="Type your message..." autocomplete="off">
                <button class="send-btn" id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Scroll to bottom of chat
        function scrollToBottom() {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        scrollToBottom();
        
        // Send message on button click or Enter key
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
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            // Add the message to the chat
                            const messageTime = new Date(res.created_at).toLocaleTimeString([], 
                                { hour: '2-digit', minute: '2-digit' });
                            const messageHtml = `
                                <div class="message ${res.sender_type}">
                                    <p>${res.message}</p>
                                    <div class="message-time">${messageTime}</div>
                                </div>
                            `;
                            $('#chat-messages').append(messageHtml);
                            
                            // Clear input
                            messageInput.val('');
                            
                            // Hide typing indicator
                            $('#typing-indicator').removeClass('active');
                            
                            scrollToBottom();
                            
                            // Simulate admin reply after a delay
                            setTimeout(simulateAdminReply, 1500);
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error sending message. Please try again.');
                        console.error('AJAX Error:', status, error);
                    },
                    complete: function() {
                        // Re-enable input
                        messageInput.prop('disabled', false).focus();
                        $('#send-btn').prop('disabled', false);
                    }
                });
            }
        }
        
        // Simulate admin reply
        function simulateAdminReply() {
            const responses = [
                "Thanks for your message. We're looking into this.",
                "I understand your concern. Let me check that for you.",
                "We appreciate your patience. Our team is working on it.",
                "Can you provide more details about the issue?",
                "I'll escalate this to our technical team."
            ];
            
            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
            
            // Show typing indicator
            $('#typing-indicator').addClass('active');
            scrollToBottom();
            
            // Delay the actual message to simulate typing
            setTimeout(function() {
                $('#typing-indicator').removeClass('active');
                
                const messageTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const messageHtml = `
                    <div class="message admin">
                        <p>${randomResponse}</p>
                        <div class="message-time">${messageTime}</div>
                    </div>
                `;
                $('#chat-messages').append(messageHtml);
                scrollToBottom();
            }, 2000);
        }
        
        // Auto-focus input on page load
        $('#message-input').focus();
    });
    </script>
</body>
</html>