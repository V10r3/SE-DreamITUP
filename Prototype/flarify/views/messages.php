<?php
require "config.php";
if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit;
}
$user = $_SESSION['user'];

$success = "";
$error = "";

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $content = trim($_POST['content']);

    if ($content) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id,receiver_id,content) VALUES (?,?,?)");
        $stmt->execute([$user['id'],$receiver_id,$content]);
        $success = "Message sent!";
        // Redirect to avoid resubmission
        header("Location: index.php?page=messages&chat=" . $receiver_id);
        exit;
    } else {
        error_log("Message send failed: Empty message from user " . $user['id']);
        $error = "Message cannot be empty.";
    }
}

// Get list of conversations (unique users I've chatted with)
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN m.sender_id = ? THEN m.receiver_id 
            ELSE m.sender_id 
        END AS other_user_id,
        u.name AS other_user_name,
        (SELECT content FROM messages 
         WHERE (sender_id = ? AND receiver_id = other_user_id) 
            OR (sender_id = other_user_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) AS last_message,
        (SELECT created_at FROM messages 
         WHERE (sender_id = ? AND receiver_id = other_user_id) 
            OR (sender_id = other_user_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) AS last_message_time
    FROM messages m
    JOIN users u ON u.id = CASE 
        WHEN m.sender_id = ? THEN m.receiver_id 
        ELSE m.sender_id 
    END
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY last_message_time DESC
");
$stmt->execute([$user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
$conversations = $stmt->fetchAll();

// Get selected conversation
$selected_user_id = $_GET['chat'] ?? ($conversations[0]['other_user_id'] ?? null);
$selected_user = null;
$chat_messages = [];

if ($selected_user_id) {
    // Get selected user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$selected_user_id]);
    $selected_user = $stmt->fetch();
    
    // Get messages for this conversation
    $stmt = $pdo->prepare("
        SELECT m.*, 
               sender.name AS sender_name,
               receiver.name AS receiver_name
        FROM messages m
        JOIN users sender ON m.sender_id = sender.id
        JOIN users receiver ON m.receiver_id = receiver.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$user['id'], $selected_user_id, $selected_user_id, $user['id']]);
    $chat_messages = $stmt->fetchAll();
}

// Get all users for new conversation (exclude current user and already chatting users)
$existing_chat_ids = array_column($conversations, 'other_user_id');
$placeholders = $existing_chat_ids ? str_repeat('?,', count($existing_chat_ids) - 1) . '?' : '';
$sql = "SELECT id, name, email, role FROM users WHERE id != ?";
if ($placeholders) {
    $sql .= " AND id NOT IN ($placeholders)";
}
$sql .= " ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$params = array_merge([$user['id']], $existing_chat_ids);
$stmt->execute($params);
$available_users = $stmt->fetchAll();
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.messages-layout {
    display: grid;
    grid-template-columns: 350px 1fr;
    height: calc(100vh - 60px);
    background: #f5f5f5;
}

.conversations-list {
    background: white;
    border-right: 1px solid #e0e0e0;
    overflow-y: auto;
}

.conversations-header {
    padding: 25px 20px;
    border-bottom: 1px solid #e0e0e0;
    background: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.conversations-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.btn-new-message {
    padding: 10px 20px;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s;
}

.btn-new-message:hover {
    transform: translateY(-2px);
}

.new-conversation-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.new-conversation-modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 25px 30px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3rem;
    color: #333;
}

.btn-close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}

.btn-close-modal:hover {
    background: #f0f0f0;
}

.modal-body {
    padding: 20px 30px;
    overflow-y: auto;
}

.user-search-input {
    width: 100%;
    padding: 12px 20px;
    border: 1px solid #ddd;
    border-radius: 25px;
    font-size: 1rem;
    margin-bottom: 20px;
    outline: none;
}

.user-search-input:focus {
    border-color: #9B59FF;
}

.user-list-item {
    padding: 15px;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: background 0.2s;
    margin-bottom: 10px;
    text-decoration: none;
    color: inherit;
}

.user-list-item:hover {
    background: #f5f5f5;
}

.user-list-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.user-list-info {
    flex: 1;
}

.user-list-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
}

.user-list-role {
    font-size: 0.85rem;
    color: #666;
    text-transform: capitalize;
}

.no-users-found {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.conversation-item {
    padding: 18px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    gap: 15px;
    align-items: center;
    text-decoration: none;
    color: inherit;
}

.conversation-item:hover {
    background: #f9f9f9;
}

.conversation-item.active {
    background: #f0e7ff;
    border-left: 3px solid #9B59FF;
}

.conversation-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.conversation-preview {
    font-size: 0.85rem;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-area {
    display: flex;
    flex-direction: column;
    background: white;
}

.chat-header {
    padding: 20px 30px;
    border-bottom: 1px solid #e0e0e0;
    background: white;
}

.chat-header h2 {
    margin: 0;
    font-size: 1.3rem;
    color: #333;
}

.chat-messages {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
    background: #fafafa;
}

.message-bubble {
    max-width: 60%;
    margin-bottom: 15px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-bubble.sent {
    margin-left: auto;
}

.message-bubble.received {
    margin-right: auto;
}

.message-content {
    padding: 15px 20px;
    border-radius: 20px;
    word-wrap: break-word;
}

.message-bubble.sent .message-content {
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    color: white;
    border-bottom-right-radius: 5px;
}

.message-bubble.received .message-content {
    background: #e9e9eb;
    color: #333;
    border-bottom-left-radius: 5px;
}

.message-time {
    font-size: 0.75rem;
    color: #999;
    margin-top: 5px;
    padding: 0 10px;
}

.chat-input-area {
    padding: 20px 30px;
    border-top: 1px solid #e0e0e0;
    background: white;
}

.chat-input-form {
    display: flex;
    gap: 15px;
    align-items: center;
}

.chat-input-form input {
    flex: 1;
    padding: 15px 20px;
    border: 1px solid #ddd;
    border-radius: 25px;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.2s;
}

.chat-input-form input:focus {
    border-color: #9B59FF;
}

.btn-send {
    padding: 15px 35px;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    color: white;
    border: none;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.btn-send:hover {
    transform: translateY(-2px);
}

.empty-chat {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #999;
}

.empty-chat i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.3;
}

.no-conversations {
    padding: 40px 20px;
    text-align: center;
    color: #999;
}
</style>

<!-- Top Navigation -->
<div class="dashboard-topbar">
    <div class="dashboard-brand">Flarify</div>
    <div class="dashboard-nav-links">
        <a href="index.php?page=dashboard">HOME</a>
        <a href="index.php?page=about">ABOUT US</a>
        <a href="index.php?page=messages" class="active">INBOX</a>
        <a href="index.php?page=dashboard">GAMES</a>
        <a href="index.php?page=logout">LOG OUT</a>
    </div>
    <div class="dashboard-search">
        <input type="text" placeholder="Search...">
        <i class="fas fa-search"></i>
    </div>
    <div class="dashboard-user-area">
        <?php include "partials/notifications.php"; ?>
        <a href="index.php?page=profile" style="text-decoration: none; color: inherit;">
            <div class="user-profile" style="cursor:pointer;">
                <div class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <span><?= htmlspecialchars($user['name']) ?></span>
            </div>
        </a>
    </div>
</div>

<!-- Main Layout -->
<div class="dashboard-layout">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <a href="index.php?page=dashboard" class="sidebar-item">
            <i class="fas fa-globe"></i>
            <span>Explore</span>
        </a>
        
        <?php if ($user['role'] === 'investor'): ?>
        <a href="index.php?page=portfolio" class="sidebar-item">
            <i class="fas fa-briefcase"></i>
            <span>Portfolio</span>
        </a>
        <a href="index.php?page=investments" class="sidebar-item">
            <i class="fas fa-chart-line"></i>
            <span>Investments</span>
        </a>
        <?php else: ?>
        <a href="index.php?page=library" class="sidebar-item">
            <i class="fas fa-book"></i>
            <span>Library</span>
        </a>
        <a href="index.php?page=collections" class="sidebar-item">
            <i class="fas fa-play-circle"></i>
            <span>Collections</span>
        </a>
        <?php endif; ?>
        
        <a href="index.php?page=messages" class="sidebar-item active">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        
        <?php if ($user['role'] === 'developer'): ?>
        <a href="index.php?page=upload" class="sidebar-item">
            <i class="fas fa-folder-plus"></i>
            <span>Created Projects</span>
        </a>
        <?php elseif ($user['role'] === 'tester'): ?>
        <a href="index.php?page=testing_queue" class="sidebar-item">
            <i class="fas fa-flask"></i>
            <span>Testing Queue</span>
        </a>
        <?php elseif ($user['role'] === 'investor'): ?>
        <a href="index.php?page=watchlist" class="sidebar-item">
            <i class="fas fa-star"></i>
            <span>Watchlist</span>
        </a>
        <?php endif; ?>
        
        <div class="sidebar-footer">
            <a href="index.php?page=settings" class="sidebar-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Messages Layout -->
    <div class="messages-layout">
        <!-- Conversations List -->
        <div class="conversations-list">
            <div class="conversations-header">
                <h2>Messages</h2>
                <button class="btn-new-message" onclick="openNewMessageModal()">
                    <i class="fas fa-plus"></i> New
                </button>
            </div>
            
            <?php if (count($conversations) === 0): ?>
            <div class="no-conversations">
                <i class="fas fa-inbox"></i>
                <p>No conversations yet</p>
            </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                <a href="index.php?page=messages&chat=<?= $conv['other_user_id'] ?>" 
                   class="conversation-item <?= $selected_user_id == $conv['other_user_id'] ? 'active' : '' ?>">
                    <div class="conversation-avatar">
                        <?= strtoupper(substr($conv['other_user_name'], 0, 1)) ?>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-name"><?= htmlspecialchars($conv['other_user_name']) ?></div>
                        <div class="conversation-preview"><?= htmlspecialchars(substr($conv['last_message'], 0, 50)) ?><?= strlen($conv['last_message']) > 50 ? '...' : '' ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <?php if ($selected_user): ?>
                <div class="chat-header">
                    <h2><?= htmlspecialchars($selected_user['name']) ?></h2>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($chat_messages as $msg): ?>
                    <div class="message-bubble <?= $msg['sender_id'] == $user['id'] ? 'sent' : 'received' ?>">
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($msg['content'])) ?>
                        </div>
                        <div class="message-time">
                            <?= date('g:i A', strtotime($msg['created_at'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="chat-input-area">
                    <form method="POST" class="chat-input-form">
                        <input type="hidden" name="receiver_id" value="<?= $selected_user['id'] ?>">
                        <input type="text" name="content" placeholder="Type your message..." required autocomplete="off">
                        <button type="submit" name="send_message" class="btn-send">Send</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <h3>Select a conversation to start chatting</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div class="new-conversation-modal" id="newMessageModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-envelope"></i> New Message</h3>
            <button class="btn-close-modal" onclick="closeNewMessageModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <input type="text" id="userSearchInput" class="user-search-input" placeholder="Search users..." onkeyup="filterUsers()">
            
            <div id="usersList">
                <?php if (count($available_users) === 0): ?>
                <div class="no-users-found">
                    <i class="fas fa-users"></i>
                    <p>You're already chatting with all users!</p>
                </div>
                <?php else: ?>
                    <?php foreach ($available_users as $available_user): ?>
                    <a href="index.php?page=messages&chat=<?= $available_user['id'] ?>" class="user-list-item" data-name="<?= strtolower(htmlspecialchars($available_user['name'])) ?>">
                        <div class="user-list-avatar">
                            <?= strtoupper(substr($available_user['name'], 0, 1)) ?>
                        </div>
                        <div class="user-list-info">
                            <div class="user-list-name"><?= htmlspecialchars($available_user['name']) ?></div>
                            <div class="user-list-role"><?= htmlspecialchars($available_user['role']) ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom of chat
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// New message modal functions
function openNewMessageModal() {
    document.getElementById('newMessageModal').classList.add('active');
}

function closeNewMessageModal() {
    document.getElementById('newMessageModal').classList.remove('active');
    document.getElementById('userSearchInput').value = '';
    filterUsers(); // Reset filter
}

// Close modal when clicking outside
document.getElementById('newMessageModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeNewMessageModal();
    }
});

// Search/filter users
function filterUsers() {
    const searchTerm = document.getElementById('userSearchInput').value.toLowerCase();
    const userItems = document.querySelectorAll('.user-list-item');
    
    userItems.forEach(item => {
        const userName = item.getAttribute('data-name');
        if (userName.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Auto-refresh chat every 5 seconds if in active conversation
<?php if ($selected_user): ?>
setInterval(function() {
    // Could implement AJAX refresh here
}, 5000);
<?php endif; ?>
</script>