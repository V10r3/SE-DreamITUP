<?php
session_start();

// If user is logged in and no page is specified, go straight to dashboard
if (isset($_SESSION['user']) && !isset($_GET['page'])) {
    $page = 'dashboard';
} else {
    $page = $_GET['page'] ?? 'login';
}

include "partials/header.php";

switch ($page) {
    case 'login':
        // If already logged in, skip login and go to dashboard
        if (isset($_SESSION['user'])) {
            include "views/dashboard.php";
        } else {
            include "views/login.php";
        }
        break;
    case 'signup': include "views/signup.php"; break;
    case 'dashboard': include "views/dashboard.php"; break;
    case 'upload': include "views/upload.php"; break;
    case 'messages': include "views/messages.php"; break;
    default: include "views/login.php"; break;
}

include "partials/footer.php";
