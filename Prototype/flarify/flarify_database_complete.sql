-- ============================================
-- FLARIFY - Game Publishing Platform
-- Complete Database Schema
-- ============================================
-- Version: 2.0
-- Last Updated: December 16, 2025
-- Author: Flarify Team
-- ============================================
-- 
-- This file contains the complete database schema for the Flarify platform.
-- Run this file to create a fresh database with all tables, indexes, and events.
-- 
-- Features:
-- - User management (developers, testers, investors)
-- - Game projects with ratings and downloads
-- - Investment tracking
-- - Testing queue workflow
-- - Collections system
-- - Password reset with auto-cleanup
-- - Notifications system
-- - Messaging system
-- 
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS Flarify;
USE Flarify;

-- ============================================
-- USERS TABLE
-- Stores user accounts (developers, testers, investors)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
  userid INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  userpassword VARCHAR(255) NOT NULL COMMENT 'Argon2ID hashed password',
  userrole ENUM('developer','tester','investor') NOT NULL,
  theme ENUM('light', 'dark', 'auto') DEFAULT 'light' COMMENT 'User theme preference',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (userrole)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROJECTS TABLE
-- Stores game projects uploaded by developers
-- ============================================
CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  developer_id INT NOT NULL,
  team_id INT DEFAULT NULL COMMENT 'Team that created this project (optional)',
  title VARCHAR(150) NOT NULL,
  projectdescription TEXT,
  credit_type ENUM('developer', 'team', 'both') DEFAULT 'developer' COMMENT 'Who gets credited',
  price DECIMAL(10,2),
  demo_flag BOOLEAN DEFAULT FALSE,
  file_path VARCHAR(255),
  banner_path VARCHAR(255) DEFAULT NULL,
  icon_path VARCHAR(255) DEFAULT NULL COMMENT 'Game icon/thumbnail',
  screenshots TEXT DEFAULT NULL COMMENT 'JSON array of screenshot paths',
  platform VARCHAR(255) DEFAULT 'Windows',
  age_rating VARCHAR(50) DEFAULT 'Everyone',
  rating DECIMAL(3,2) DEFAULT 0.00 COMMENT 'Average rating (0-5)',
  total_ratings INT DEFAULT 0,
  downloads INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (developer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL,
  INDEX idx_developer (developer_id),
  INDEX idx_team (team_id),
  INDEX idx_rating (rating),
  INDEX idx_platform (platform),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROJECT RATINGS TABLE
-- Tracks individual user ratings for games
-- ============================================
CREATE TABLE IF NOT EXISTS project_ratings (
  ratingid INT AUTO_INCREMENT PRIMARY KEY,
  projectid INT NOT NULL,
  foreign key (projectid) references projects(projectid) on update cascade on delete cascade,
  userid INT NOT NULL,
  foreign key (userid) references users(userid) on update cascade on delete cascade,
  rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_project (user_id, project_id),
  INDEX idx_project (project_id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WATCHLIST TABLE
-- Allows investors to track games they're interested in
-- ============================================
CREATE TABLE IF NOT EXISTS watchlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  project_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_project (user_id, project_id),
  INDEX idx_user (user_id),
  INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INVESTMENTS TABLE
-- Tracks investor funding of game projects
-- ============================================
CREATE TABLE IF NOT EXISTS investments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  investor_id INT NOT NULL,
  project_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  equity_percentage DECIMAL(5,2) DEFAULT NULL COMMENT 'Optional: percentage ownership',
  investmentstatus ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'active',
  notes TEXT DEFAULT NULL,
  invested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (investor_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  INDEX idx_investor (investor_id),
  INDEX idx_project (project_id),
  INDEX idx_status (investmentstatus),
  INDEX idx_invested (invested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MESSAGES TABLE
-- Stores direct messages between users
-- ============================================
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_sender (sender_id),
  INDEX idx_receiver (receiver_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATIONS TABLE
-- Stores user notifications (messages, ratings, investments, system)
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  notificationtype VARCHAR(50) NOT NULL COMMENT 'message, rating, investment, download, system',
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  link VARCHAR(255) DEFAULT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_read (user_id, is_read),
  INDEX idx_type (notificationtype),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASSWORD RESETS TABLE
-- Stores temporary password reset tokens (15 minute expiry)
-- ============================================
CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- COLLECTIONS TABLE
-- User-created collections to organize games
-- ============================================
CREATE TABLE IF NOT EXISTS collections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  collectionname VARCHAR(100) NOT NULL,
  collectiondescription TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- COLLECTION ITEMS TABLE
-- Games within collections (many-to-many)
-- ============================================
CREATE TABLE IF NOT EXISTS collection_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  collection_id INT NOT NULL,
  project_id INT NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  UNIQUE KEY unique_collection_item (collection_id, project_id),
  INDEX idx_collection (collection_id),
  INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TESTING QUEUE TABLE
-- Games in tester's workflow
-- ============================================
CREATE TABLE IF NOT EXISTS testing_queue (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tester_id INT NOT NULL,
  project_id INT NOT NULL,
  testingstatus ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
  notes TEXT,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  FOREIGN KEY (tester_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  UNIQUE KEY unique_test_item (tester_id, project_id),
  INDEX idx_tester (tester_id),
  INDEX idx_status (testingstatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TEAMS TABLE
-- Developer teams for collaborative projects
-- ============================================
CREATE TABLE IF NOT EXISTS teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_name VARCHAR(150) NOT NULL,
  teamdescription TEXT,
  owner_id INT NOT NULL COMMENT 'User who created the team',
  avatar_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TEAM MEMBERS TABLE
-- Maps developers to teams (many-to-many)
-- ============================================
CREATE TABLE IF NOT EXISTS team_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  user_id INT NOT NULL,
  memberrole ENUM('owner', 'admin', 'member') DEFAULT 'member',
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_team_member (team_id, user_id),
  INDEX idx_team (team_id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EVENTS - Automated Tasks
-- ============================================

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- Automated cleanup of expired password reset tokens (runs every 5 minutes)
DROP EVENT IF EXISTS cleanup_expired_password_resets;
CREATE EVENT cleanup_expired_password_resets
ON SCHEDULE EVERY 5 MINUTE
DO
  DELETE FROM password_resets WHERE expires_at < NOW();

-- ============================================
-- SAMPLE DATA (Optional - Comment out for production)
-- ============================================

-- Sample developer account
-- Password: developer123 (hashed with Argon2ID)
-- INSERT INTO users (name, email, password, role) VALUES 
-- ('Sample Developer', 'dev@flarify.com', '$argon2id$v=19$m=65536,t=4,p=1$...', 'developer');

-- Sample tester account
-- Password: tester123
-- INSERT INTO users (name, email, password, role) VALUES 
-- ('Sample Tester', 'tester@flarify.com', '$argon2id$v=19$m=65536,t=4,p=1$...', 'tester');

-- Sample investor account
-- Password: investor123
-- INSERT INTO users (name, email, password, role) VALUES 
-- ('Sample Investor', 'investor@flarify.com', '$argon2id$v=19$m=65536,t=4,p=1$...', 'investor');

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================

SELECT 'Database setup completed successfully!' as message;
