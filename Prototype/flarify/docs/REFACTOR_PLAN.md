# File Structure Reorganization Plan

## Current Issues
- Legacy files in root (dashboard.php, explore.php, game.php, game-detail.php, messages.php, signup.php, upload.php, logout.php)
- Duplicate config files (config.php in root AND includes/config.php)
- Legacy process files (login-process.php, signup-process.php, message-process.php, invest-process.php, role-process.php)
- Multiple CSS folders (assets/, css/)
- Test/debug files in root

## Proposed New Structure

```
flarify/
├── index.php                    (Main router - KEEP)
├── config.php                   (Main config - KEEP)
├── .htaccess                    (Apache config - KEEP)
├── flarify_database_complete.sql (Database schema - KEEP)
│
├── assets/                      (Consolidated assets)
│   ├── css/
│   │   ├── dashboard-styles.css
│   │   ├── login-page-styles.css
│   │   └── style.css
│   └── js/
│       └── script.js
│
├── backend/                     (Backend API/Logic)
│   ├── auth_login.php
│   ├── auth_signup.php
│   ├── collections.php
│   ├── download_game.php
│   ├── email_helper.php
│   ├── init.php
│   ├── investments.php
│   ├── logout.php
│   ├── notification_helper.php
│   ├── notifications.php
│   ├── rate_game.php
│   ├── request_reset.php
│   ├── reset.php
│   ├── send_message.php
│   ├── settings.php
│   ├── testing_queue.php
│   ├── upload_game.php
│   └── watchlist.php
│
├── views/                       (All view files)
│   ├── about.php
│   ├── collections.php
│   ├── contact.php
│   ├── dashboard_developer.php
│   ├── dashboard_investor.php
│   ├── dashboard_tester.php
│   ├── edit.php
│   ├── game.php
│   ├── investments.php
│   ├── library.php
│   ├── login.php
│   ├── messages.php
│   ├── portfolio.php
│   ├── profile.php
│   ├── settings.php
│   ├── signup.php
│   ├── teams.php
│   ├── testing_queue.php
│   ├── upload.php
│   └── watchlist.php
│
├── partials/                    (Reusable components)
│   ├── header.php
│   ├── footer.php
│   └── notifications.php
│
├── uploads/                     (User uploaded files)
│
├── docs/                        (Documentation)
│   ├── EMAIL_SETUP_GUIDE.md
│   └── TEAM_FUNCTIONALITY.md
│
└── _archive/                    (Legacy files - to be removed)
    ├── dashboard.php
    ├── explore.php
    ├── game.php
    ├── game-detail.php
    ├── messages.php
    ├── signup.php
    ├── upload.php
    ├── logout.php
    ├── login-process.php
    ├── signup-process.php
    ├── message-process.php
    ├── invest-process.php
    ├── role-process.php
    ├── role-selection.php
    ├── css/ (old folder)
    ├── includes/
    ├── js/ (if different from assets/js)
    ├── check_*.php (debug files)
    ├── test_*.php (test files)
    └── setup_teams.php (one-time script)
```

## Changes Required

### 1. Create new folders
- `assets/css/` - consolidate CSS
- `assets/js/` - consolidate JS
- `docs/` - for documentation
- `_archive/` - for legacy files

### 2. Move files
- Move css/style.css → assets/css/style.css
- Move js/script.js → assets/js/script.js
- Move *.md → docs/
- Move legacy files → _archive/

### 3. Update references
- Update all CSS links to point to assets/css/
- Update all JS links to point to assets/js/
- Verify config.php paths are correct

### 4. Clean up
- Remove includes/ folder (duplicate config)
- Archive test/debug scripts
- Remove one-time setup scripts after running
