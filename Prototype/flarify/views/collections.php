<?php
/**
 * Collections View
 * 
 * Game collections management interface.
 * Allows users to organize games into custom collections.
 * 
 * Features:
 * - Create new collections
 * - View all collections with game counts
 * - Delete collections
 * - Add/remove games from collections
 * 
 * @package Flarify
 * @author Flarify Team
 */

require "config.php";

// Authentication check
if (!isset($_SESSION['user'])) {
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
        <a href="index.php?page=collections" class="sidebar-item active">
            <i class="fas fa-play-circle"></i>
            <span>Collections</span>
        </a>
        <?php if ($user['userrole'] === 'developer'): ?>
        <a href="index.php?page=teams" class="sidebar-item">
            <i class="fas fa-users"></i>
            <span>Teams</span>
        </a>
        <?php endif; ?>
        <a href="index.php?page=messages" class="sidebar-item">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        <?php if ($user['userrole'] === 'developer'): ?>
        <a href="index.php?page=upload" class="sidebar-item">
            <i class="fas fa-folder-plus"></i>
            <span>Created Projects</span>
        </a>
        <?php elseif ($user['userrole'] === 'tester'): ?>
        <a href="index.php?page=testing_queue" class="sidebar-item">
            <i class="fas fa-flask"></i>
            <span>Testing Queue</span>
        </a>
        <?php endif; ?>
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
            <h1><i class="fas fa-folder-open"></i> My Collections</h1>
            <button onclick="showCreateModal()" style="padding: 12px 24px; background: linear-gradient(135deg, #9B59FF, #7B3FF2); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-plus"></i> New Collection
            </button>
        </div>

        <div id="collectionsContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
            <!-- Collections will be loaded here -->
        </div>
        
        <div id="emptyState" style="display: none; text-align: center; padding: 60px 20px; color: #999;">
            <i class="fas fa-folder-open" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">No Collections Yet</h3>
            <p>Create your first collection to organize your favorite games!</p>
        </div>
    </div>
</div>

<!-- Create Collection Modal -->
<div id="createModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; max-width: 500px; width: 90%;">
        <h2 style="margin: 0 0 20px 0; color: #333;"><i class="fas fa-folder-plus"></i> Create Collection</h2>
        <form id="createForm" onsubmit="createCollection(event)">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">Collection Name</label>
                <input type="text" id="collectionName" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">Description (Optional)</label>
                <textarea id="collectionDescription" rows="3" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; resize: vertical;"></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeCreateModal()" style="padding: 12px 24px; background: #f0f0f0; color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #9B59FF, #7B3FF2); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Create</button>
            </div>
        </form>
    </div>
</div>

<style>
.collection-card {
    background: white;
    border-radius: 15px;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.collection-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(155, 89, 255, 0.2);
}

.collection-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #9B59FF, #7B3FF2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    margin-bottom: 15px;
}

.collection-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.collection-count {
    color: #666;
    font-size: 0.9rem;
}

.collection-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}

.collection-actions button {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9rem;
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
function loadCollections() {
    fetch('backend/collections.php?action=list')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('collectionsContainer');
                const emptyState = document.getElementById('emptyState');
                
                if (data.collections.length === 0) {
                    container.style.display = 'none';
                    emptyState.style.display = 'block';
                } else {
                    container.style.display = 'grid';
                    emptyState.style.display = 'none';
                    
                    container.innerHTML = data.collections.map(collection => `
                        <div class="collection-card" onclick="viewCollection(${collection.id})">
                            <div class="collection-icon">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div class="collection-title">${escapeHtml(collection.name)}</div>
                            <div class="collection-count">${collection.game_count} game${collection.game_count !== 1 ? 's' : ''}</div>
                            ${collection.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 8px;">${escapeHtml(collection.description)}</p>` : ''}
                            <div class="collection-actions" onclick="event.stopPropagation()">
                                <button onclick="viewCollection(${collection.id})" style="background: #9B59FF; color: white; flex: 1;">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button onclick="deleteCollection(${collection.id})" style="background: #f44336; color: white;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
                }
            }
        })
        .catch(error => console.error('Error loading collections:', error));
}

function showCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
    document.getElementById('createForm').reset();
}

function createCollection(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', document.getElementById('collectionName').value);
    formData.append('description', document.getElementById('collectionDescription').value);
    
    fetch('backend/collections.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Collection created successfully!', 'success');
            closeCreateModal();
            loadCollections();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to create collection', 'error');
    });
}

function deleteCollection(id) {
    if (!confirm('Are you sure you want to delete this collection? This will not delete the games.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('collection_id', id);
    
    fetch('backend/collections.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Collection deleted', 'success');
            loadCollections();
        } else {
            showToast(data.message, 'error');
        }
    });
}

function viewCollection(id) {
    window.location.href = 'index.php?page=collection_detail&id=' + id;
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

// Load collections on page load
loadCollections();
</script>
