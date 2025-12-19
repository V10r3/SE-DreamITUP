# Team Functionality Implementation Guide

## Overview
This document describes the team collaboration feature added to Flarify, allowing developers to create teams, add members, and choose credit attribution for their games.

## Database Changes

### New Tables

#### 1. `teams` Table
```sql
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL UNIQUE,
    teamdescription TEXT,
    owner_id INT NOT NULL,
    avatar_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
)
```

#### 2. `team_members` Table
```sql
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    memberrole ENUM('owner', 'admin', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member (team_id, user_id)
)
```

### Modified Tables

#### `projects` Table - New Columns
- `team_id INT DEFAULT NULL` - Links project to a team (optional)
- `credit_type ENUM('developer', 'team', 'both') DEFAULT 'developer'` - Controls credit attribution
- Foreign key constraint: `FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL`

## Setup Instructions

1. **Run Database Migration**
   ```bash
   php setup_teams.php
   ```
   This creates the new tables and adds columns to the projects table.

2. **Access Teams Page**
   - Navigate to `index.php?page=teams`
   - Developers will see a "Teams" link in their sidebar

## Features Implemented

### 1. Team Management (`views/teams.php`)
- **Create Team**: Developers can create new teams with name and description
- **View Teams**: Lists all teams the user is a member of
- **Team Stats**: Shows member count and project count for each team
- **Add Members**: Owners and admins can add new members by email address
- **Role Badges**: Visual indicators for owner/admin/member roles

### 2. Upload with Team Credit (`views/upload.php`)
- **Team Selection**: Dropdown to select which team the project belongs to
- **Credit Options**: Radio buttons to choose:
  - "Just Me" - Credits only the developer
  - "Just the Team" - Credits only the team
  - "Both Me & Team" - Credits both developer and team
- **Validation**: Ensures user is actually a member of the selected team

### 3. Edit with Team Credit (`views/edit.php`)
- Same team selection and credit options as upload
- Pre-populates existing team and credit type values
- Allows changing team attribution for existing projects

### 4. Game Display (`views/game.php`)
- Shows credits based on `credit_type`:
  - `developer`: "by [Developer Name]"
  - `team`: "by 👥 [Team Name]"
  - `both`: "by [Developer Name] & 👥 [Team Name]"
- Team names link to teams page

### 5. Dashboard Integration (`views/dashboard_developer.php`)
- Shows team badge on game cards when team is credited
- Updated query to fetch team information

### 6. Navigation Updates
Added "Teams" link to sidebars in:
- `views/teams.php` (active)
- `views/upload.php`
- `views/library.php` (developer only)
- `views/dashboard_developer.php`

## File Changes Summary

### Modified Files
1. `index.php` - Added teams route
2. `flarify_database_complete.sql` - Added team tables and project columns
3. `views/upload.php`:
   - Fetch user's teams
   - Team selection dropdown
   - Credit type radio buttons
   - Updated INSERT query with team_id and credit_type
   - JavaScript for toggling credit options
4. `views/edit.php`:
   - Fetch user's teams
   - Team selection dropdown
   - Credit type radio buttons
   - Updated UPDATE query with team_id and credit_type
   - JavaScript for toggling credit options
5. `views/game.php`:
   - Updated query to JOIN teams table
   - Dynamic credit display based on credit_type
6. `views/dashboard_developer.php`:
   - Updated query to JOIN teams table
   - Team badge display on game cards
7. `views/library.php`:
   - Added Teams navigation link

### New Files
1. `views/teams.php` - Complete team management interface
2. `setup_teams.php` - Database migration script

## Usage Workflow

### Creating a Team
1. Navigate to Teams page
2. Click "Create New Team"
3. Enter team name and description
4. Click "Create Team"
5. Team owner is automatically added with 'owner' role

### Adding Team Members
1. Go to Teams page
2. Find your team
3. Enter member's email address
4. Click "Add Member"
5. Member is added with 'member' role

### Uploading with Team Credit
1. Go to Upload page
2. Select game file and fill in details
3. In "Team Credit Options":
   - Select a team from dropdown (or leave as "No Team")
   - If team selected, choose credit type
4. Upload game

### Editing Team Credit
1. Go to Library and click Edit on a game
2. Scroll to "Team Credit Options"
3. Change team or credit type
4. Save changes

## Credit Type Display Logic

| credit_type | Display Result |
|-------------|---------------|
| `developer` | "by John Doe" |
| `team` | "by 👥 Alpha Team" |
| `both` | "by John Doe & 👥 Alpha Team" |
| NULL (no team) | "by John Doe" |

## Permissions

### Team Roles
- **Owner**: Can add members, delete team (implicit)
- **Admin**: Can add members
- **Member**: Can view team, be credited on projects

### Project Permissions
- Only team members can assign a project to that team
- Validation happens on both upload and edit

## Future Enhancements

Potential additions:
1. **Team Profile Page**: Dedicated page showing team details and projects
2. **Member Management**: Remove members, change roles
3. **Team Avatar Upload**: Custom team avatars
4. **Team Invitations**: Email invitations instead of direct adds
5. **Team Settings**: Privacy settings, team description editing
6. **Team Statistics**: Downloads, ratings for team projects
7. **Team Chat**: Built-in communication for team members

## Testing Checklist

- [ ] Create a new team
- [ ] Add a member to the team
- [ ] Upload a game with team credit (all 3 types)
- [ ] Edit existing game to change team credit
- [ ] View game page and verify correct credit display
- [ ] Check dashboard shows team badges
- [ ] Verify navigation links work
- [ ] Test team validation (can't assign to non-member team)

## Notes

- Team functionality is only visible to developers
- Teams are created per user, not globally
- One user can be in multiple teams
- One project can only be assigned to one team
- Deleting a team sets project team_id to NULL (ON DELETE SET NULL)
- Deleting a user removes them from teams (ON DELETE CASCADE)
