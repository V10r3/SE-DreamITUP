<?php
/**
 * Header Partial
 * 
 * Contains user session check and theme initialization.
 * Included in all pages that use the standard layout.
 */

// Get user session data
$user = $_SESSION['user'] ?? null;

// Determine theme-based styling
$userTheme = $user['theme'] ?? 'light';
$bgColor = '#f5f5f5';
$textColor = '#333333';

// Apply background colors based on theme preference
if ($userTheme === 'dark') {
    $bgColor = '#1a1a1a';
    $textColor = '#ffffff';
} elseif ($userTheme === 'auto') {
    // For auto mode, default to light (JavaScript will override if system prefers dark)
    $bgColor = '#f5f5f5';
    $textColor = '#333333';
}
?>

<!-- Inline style to apply theme immediately (prevents flash) -->
<style>
    <?php if ($userTheme === 'dark'): ?>
    /* Dark mode - only apply to body background */
    body {
        background-color: #1a1a1a !important;
    }
    
    /* Keep sidebar and topbar with original styling */
    .dashboard-sidebar, .dashboard-sidebar *,
    .dashboard-topbar, .dashboard-topbar * {
        background-color: initial !important;
        color: initial !important;
    }
    
    /* Keep all white card sections readable (settings, etc) */
    div[style*="background:white"], 
    div[style*="background: white"],
    .settings-card {
        background: white !important;
        color: #333 !important;
    }
    
    div[style*="background:white"] *,
    div[style*="background: white"] *,
    .settings-card * {
        color: inherit !important;
    }
    
    /* Only apply dark styling to game cards */
    .game-card {
        background: #2a2a2a !important;
    }
    .game-card * {
        color: #ffffff !important;
    }
    <?php else: ?>
    /* Light mode default */
    body {
        background-color: #f5f5f5 !important;
        color: #333333 !important;
    }
    <?php endif; ?>
</style>

<script>
/**
 * Theme Application Script
 * 
 * Applies theme dynamically and handles auto mode based on system preference.
 * Runs on every page load to ensure consistent theming.
 */
(function() {
    const userTheme = '<?= $userTheme ?>';
    
    function applyTheme(theme) {
        // Handle auto mode by detecting system preference
        if (theme === 'auto') {
            const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            theme = isDark ? 'dark' : 'light';
        }
        
        // Apply theme colors - minimal approach
        if (theme === 'dark') {
            // Only change body background
            document.body.style.backgroundColor = '#1a1a1a';
            
            // Apply dark styling only to game cards
            document.querySelectorAll('.game-card').forEach(el => {
                el.style.backgroundColor = '#2a2a2a';
                el.querySelectorAll('*').forEach(child => {
                    child.style.color = '#ffffff';
                });
            });
            
            // Keep white cards white (settings, etc)
            document.querySelectorAll('div[style*="background:white"], div[style*="background: white"]').forEach(el => {
                el.style.backgroundColor = 'white';
                el.style.color = '#333';
            });
        } else {
            // Light mode
            document.body.style.backgroundColor = '#f5f5f5';
            document.body.style.color = '#333333';
        }
    }
    
    // Apply theme immediately on script load
    applyTheme(userTheme);
    
    // Reapply after DOM is fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => applyTheme(userTheme));
    }
    
    // Listen for system theme changes (for auto mode)
    if (userTheme === 'auto') {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            applyTheme('auto');
        });
    }
})();
</script>