<!-- Notification Bell with Dropdown -->
<div class="notification-container">
    <button class="icon-button" id="notificationBell" onclick="toggleNotifications()">
        <i class="fas fa-bell"></i>
        <span class="notification-badge" id="notificationBadge" style="display:none;">0</span>
    </button>
    
    <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
            <h3>Notifications</h3>
            <button onclick="markAllAsRead()" class="btn-mark-read">Mark all as read</button>
        </div>
        <div class="notification-list" id="notificationList">
            <div class="notification-loading">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>
</div>

<style>
.notification-container {
    position: relative;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4757;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.notification-dropdown {
    position: absolute;
    top: calc(100% + 15px);
    right: 0;
    width: 380px;
    max-height: 500px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    display: none;
    flex-direction: column;
    z-index: 1000;
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.notification-dropdown.active {
    display: flex;
}

.notification-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #333;
}

.btn-mark-read {
    background: none;
    border: none;
    color: #9B59FF;
    font-size: 0.85rem;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 5px;
    transition: background 0.2s;
}

.btn-mark-read:hover {
    background: #f0e7ff;
}

.notification-list {
    overflow-y: auto;
    max-height: 400px;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.notification-item:hover {
    background: #f9f9f9;
}

.notification-item.unread {
    background: #f0e7ff;
}

.notification-item.unread:hover {
    background: #e8d9ff;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
}

.notification-icon.message {
    background: #e3f2fd;
    color: #2196f3;
}

.notification-icon.rating {
    background: #fff3e0;
    color: #ff9800;
}

.notification-icon.download {
    background: #e8f5e9;
    color: #4caf50;
}

.notification-icon.system {
    background: #f3e5f5;
    color: #9c27b0;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
    font-size: 0.9rem;
}

.notification-message {
    color: #666;
    font-size: 0.85rem;
    line-height: 1.4;
}

.notification-time {
    color: #999;
    font-size: 0.75rem;
    margin-top: 4px;
}

.notification-loading,
.notification-empty {
    padding: 40px 20px;
    text-align: center;
    color: #999;
}

.notification-empty i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 10px;
}
</style>

<script>
let notificationInterval;

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    const isActive = dropdown.classList.contains('active');
    
    if (isActive) {
        dropdown.classList.remove('active');
        clearInterval(notificationInterval);
    } else {
        dropdown.classList.add('active');
        loadNotifications();
        // Auto-refresh every 10 seconds when open
        notificationInterval = setInterval(loadNotifications, 10000);
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const container = document.querySelector('.notification-container');
    const dropdown = document.getElementById('notificationDropdown');
    if (container && !container.contains(e.target) && dropdown.classList.contains('active')) {
        dropdown.classList.remove('active');
        clearInterval(notificationInterval);
    }
});

function loadNotifications() {
    fetch('backend/notifications.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.notifications);
                updateBadge(data.unread_count);
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

function displayNotifications(notifications) {
    const list = document.getElementById('notificationList');
    
    if (notifications.length === 0) {
        list.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications yet</p>
            </div>
        `;
        return;
    }
    
    list.innerHTML = notifications.map(notif => `
        <div class="notification-item ${!notif.is_read ? 'unread' : ''}" 
             onclick="handleNotificationClick(${notif.id}, '${notif.link || ''}')">
            <div class="notification-icon ${notif.type}">
                ${getNotificationIcon(notif.type)}
            </div>
            <div class="notification-content">
                <div class="notification-title">${escapeHtml(notif.title)}</div>
                <div class="notification-message">${escapeHtml(notif.message)}</div>
                <div class="notification-time">${formatTime(notif.created_at)}</div>
            </div>
        </div>
    `).join('');
}

function getNotificationIcon(type) {
    const icons = {
        'message': '<i class="fas fa-envelope"></i>',
        'rating': '<i class="fas fa-star"></i>',
        'download': '<i class="fas fa-download"></i>',
        'system': '<i class="fas fa-info-circle"></i>'
    };
    return icons[type] || '<i class="fas fa-bell"></i>';
}

function updateBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (count > 0) {
        badge.textContent = count > 9 ? '9+' : count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

function handleNotificationClick(notifId, link) {
    // Mark as read
    const formData = new FormData();
    formData.append('notification_id', notifId);
    
    fetch('backend/notifications.php?action=mark_read', {
        method: 'POST',
        body: formData
    }).then(() => {
        loadNotifications();
        if (link) {
            window.location.href = link;
        }
    });
}

function markAllAsRead() {
    fetch('backend/notifications.php?action=mark_all_read', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        });
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load badge count on page load
loadNotifications();

// Check for new notifications every 30 seconds
setInterval(loadNotifications, 30000);
</script>
