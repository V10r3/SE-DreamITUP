<?php
/**
 * Main Application Router
 * 
 * This is the entry point for the Flarify application.
 * Handles session initialization, routing, and layout management.
 * 
 * @package Flarify
 * @author Flarify Team
 */

// Initialize session for user authentication
session_start();

// Load database configuration
require "config.php";

// Get requested page from URL parameter (default: login)
$page = $_GET['page'] ?? 'login';

// Pages that don't use the standard header/footer layout
$hideLayout = in_array($page, ['login','signup','about','contact','game']);

// Include header for pages that use standard layout
if (!$hideLayout) include "partials/header.php";

switch ($page) {
  case 'login': include "views/login.php"; break;
  case 'signup': include "views/signup.php"; break;
  case 'about': include "views/about.php"; break;
  case 'contact': include "views/contact.php"; break;
  case 'dashboard':
    if (isset($_SESSION['user']['userrole'])) {
      if ($_SESSION['user']['userrole'] === 'developer') include "views/dashboard_developer.php";
      elseif ($_SESSION['user']['userrole'] === 'tester') include "views/dashboard_tester.php";
      elseif ($_SESSION['user']['userrole'] === 'investor') include "views/dashboard_investor.php";
    } else {
      header("Location: index.php?page=login");
      exit;
    }
    break;
  case 'upload': include "views/upload.php"; break;
  case 'library': include "views/library.php"; break;
  case 'collections': include "views/collections.php"; break;
  case 'teams': include "views/teams.php"; break;
  case 'testing_queue': include "views/testing_queue.php"; break;
  case 'watchlist': include "views/watchlist.php"; break;
  case 'portfolio': include "views/portfolio.php"; break;
  case 'investments': include "views/investments.php"; break;
  case 'edit': include "views/edit.php"; break;
  case 'game': include "views/game.php"; break;
  case 'profile': include "views/profile.php"; break;
  case 'settings': include "views/settings.php"; break;
  case 'messages': include "views/messages.php"; break;
  case 'logout':
  include "backend/logout.php";  break;
  default: include "views/login.php"; break;
}

if (!$hideLayout) include "partials/footer.php";