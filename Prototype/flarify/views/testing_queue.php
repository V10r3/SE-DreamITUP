<?php
require "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['userrole'] !== 'tester') {
    header("Location: index.php?page=login");
    exit;
}
$user = $_SESSION['user'];
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Top Navigation -->
<div class="dashboard-topbar">
    <div class="dashboard-brand">Flarify</div>
    <div class="dashboard-nav-links">
        <a href="index.php?page=dashboard">HOME</a>
        <a href="index.php?page=about">ABOUT US</a>
        <a href="index.php?page=dashboard">GAMES</a>
        <a href="index.php?page=logout">LOG OUT</a>
    </div>
    <div class="dashboard-search">
        <input type="text" placeholder="Search...">
        <i class="fas fa-search"></i>
    </div>
    <div class="dashboard-user-area">
        <?php include "partials/notifications.php"; ?>
        <div class="user-profile" style="cursor:pointer;" onclick="window.location.href='index.php?page=profile'">
            <div class="user-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
            <span><?= htmlspecialchars($user['username']) ?></span>
        </div>
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
        <a href="index.php?page=library" class="sidebar-item">
            <i class="fas fa-book"></i>
            <span>Library</span>
        </a>
        <a href="index.php?page=collections" class="sidebar-item">
            <i class="fas fa-play-circle"></i>
            <span>Collections</span>
        </a>
        <a href="index.php?page=messages" class="sidebar-item">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        <a href="index.php?page=testing_queue" class="sidebar-item active">
            <i class="fas fa-flask"></i>
            <span>Testing Queue</span>
        </a>
        <div class="sidebar-footer">
            <a href="index.php?page=settings" class="sidebar-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <div class="welcome-header">
            <h1><i class="fas fa-flask"></i> Testing Queue</h1>
            <p>Manage your game testing pipeline</p>
        </div>

        <!-- Filter Tabs -->
        <div style="display: flex; gap: 10px; margin: 20px 0; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <button onclick="filterQueue('all')" class="filter-tab active" data-filter="all">
                All <span class="count" id="countAll">0</span>
            </button>
            <button onclick="filterQueue('pending')" class="filter-tab" data-filter="pending">
                <i class="fas fa-clock"></i> Pending <span class="count" id="countPending">0</span>
            </button>
            <button onclick="filterQueue('in_progress')" class="filter-tab" data-filter="in_progress">
                <i class="fas fa-play"></i> Testing <span class="count" id="countInProgress">0</span>
            </button>
            <button onclick="filterQueue('completed')" class="filter-tab" data-filter="completed">
                <i class="fas fa-check"></i> Completed <span class="count" id="countCompleted">0</span>
            </button>
        </div>

        <div id="queueContainer">
            <!-- Queue items will be loaded here -->
        </div>
        
        <div id="emptyState" style="display: none; text-align: center; padding: 60px 20px; color: #999;">
            <i class="fas fa-flask" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">No Games in Queue</h3>
            <p>Add games from the Explore page to start testing!</p>
        </div>
    </div>
</div>

<style>
.filter-tab {
    padding: 10px 20px;
    background: transparent;
    border: none;
    color: #666;
    font-weight: 600;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.filter-tab:hover {
    color: #9B59FF;
}

.filter-tab.active {
    color: #9B59FF;
    border-bottom-color: #9B59FF;
}

.filter-tab .count {
    background: #eee;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.85rem;
    margin-left: 5px;
}

.filter-tab.active .count {
    background: #9B59FF;
    color: white;
}

.queue-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.queue-thumb {
    width: 120px;
    height: 90px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.queue-info {
    flex: 1;
}

.queue-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.queue-dev {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.queue-notes {
    color: #666;
    font-size: 0.9rem;
    font-style: italic;
    margin-top: 10px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 6px;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-in_progress {
    background: #cfe2ff;
    color: #084298;
}

.status-completed {
    background: #d1e7dd;
    color: #0f5132;
}

.queue-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}

.queue-actions button {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

@keyframes slideIn {
  from { transform: translateX(400px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOut {
  from { transform: translateX(0); opacity: 1; }
  to { transform: translateX(400px); opacity: 0; }
}
</style>

<script>
let currentFilter = 'all';
let queueData = [];

function loadQueue(filter = 'all') {
    fetch(`backend/testing_queue.php?action=list&status=${filter}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                queueData = data.queue;
                currentFilter = filter;
                renderQueue();
                updateCounts();
            }
        })
        .catch(error => console.error('Error loading queue:', error));
}

function renderQueue() {
    const container = document.getElementById('queueContainer');
    const emptyState = document.getElementById('emptyState');
    
    const filteredQueue = currentFilter === 'all' 
        ? queueData 
        : queueData.filter(item => item.status === currentFilter);
    
    if (filteredQueue.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
    } else {
        container.style.display = 'block';
        emptyState.style.display = 'none';
        
        container.innerHTML = filteredQueue.map(item => {
            const thumb = item.icon_path || item.banner_path || (item.screenshots ? JSON.parse(item.screenshots)[0] : '');
            const thumbHtml = thumb 
                ? `<img src="${thumb}" class="queue-thumb" alt="${escapeHtml(item.title)}">`
                : `<div class="queue-thumb" style="background: linear-gradient(135deg, #9B59FF, #7B3FF2); display: flex; align-items: center; justify-content: center; color: white;"><i class="fas fa-gamepad" style="font-size: 2rem;"></i></div>`;
            
            return `
                <div class="queue-item">
                    ${thumbHtml}
                    <div class="queue-info">
                        <div class="queue-title">${escapeHtml(item.title)}</div>
                        <div class="queue-dev">by ${escapeHtml(item.dev_name)}</div>
                        <span class="status-badge status-${item.status}">${formatStatus(item.status)}</span>
                        ${item.notes ? `<div class="queue-notes"><i class="fas fa-sticky-note"></i> ${escapeHtml(item.notes)}</div>` : ''}
                        <div style="margin-top: 10px; font-size: 0.85rem; color: #999;">
                            Added: ${new Date(item.added_at).toLocaleDateString()}
                            ${item.completed_at ? ` | Completed: ${new Date(item.completed_at).toLocaleDateString()}` : ''}
                        </div>
                    </div>
                    <div class="queue-actions">
                        <button onclick="window.location.href='index.php?page=game&id=${item.project_id}'" style="background: #9B59FF; color: white;">
                            <i class="fas fa-eye"></i> View
                        </button>
                        ${item.status === 'pending' ? `<button onclick="updateStatus(${item.id}, 'in_progress')" style="background: #4CAF50; color: white;"><i class="fas fa-play"></i> Start</button>` : ''}
                        ${item.status === 'in_progress' ? `<button onclick="updateStatus(${item.id}, 'completed')" style="background: #4CAF50; color: white;"><i class="fas fa-check"></i> Complete</button>` : ''}
                        ${item.status === 'completed' ? `<button onclick="updateStatus(${item.id}, 'pending')" style="background: #FF9800; color: white;"><i class="fas fa-redo"></i> Retest</button>` : ''}
                        <button onclick="removeFromQueue(${item.id})" style="background: #f44336; color: white;">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }
}

function updateCounts() {
    document.getElementById('countAll').textContent = queueData.length;
    document.getElementById('countPending').textContent = queueData.filter(i => i.status === 'pending').length;
    document.getElementById('countInProgress').textContent = queueData.filter(i => i.status === 'in_progress').length;
    document.getElementById('countCompleted').textContent = queueData.filter(i => i.status === 'completed').length;
}

function filterQueue(status) {
    // Update active tab
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-filter="${status}"]`).classList.add('active');
    
    currentFilter = status;
    renderQueue();
}

function updateStatus(queueId, newStatus) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('queue_id', queueId);
    formData.append('status', newStatus);
    
    fetch('backend/testing_queue.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Status updated!', 'success');
            loadQueue(currentFilter);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function removeFromQueue(queueId) {
    if (!confirm('Remove this game from your testing queue?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('queue_id', queueId);
    
    fetch('backend/testing_queue.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Removed from queue', 'success');
            loadQueue(currentFilter);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function formatStatus(status) {
    const map = {
        'pending': 'Pending',
        'in_progress': 'Testing',
        'completed': 'Completed'
    };
    return map[status] || status;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'linear-gradient(135deg, #4CAF50, #81C784)' : 'linear-gradient(135deg, #f44336, #ef5350)';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.innerHTML = `<i class="fas ${icon}" style="margin-right: 10px; font-size: 1.2rem;"></i><span>${message}</span>`;
    
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: bgColor,
        color: 'white',
        padding: '16px 24px',
        borderRadius: '10px',
        boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
        zIndex: '10000',
        display: 'flex',
        alignItems: 'center',
        fontWeight: '500',
        animation: 'slideIn 0.3s ease-out'
    });
    
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Load queue on page load
loadQueue('all');
</script>
