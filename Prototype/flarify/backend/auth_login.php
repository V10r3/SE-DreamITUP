<?php
/**
 * User Login Handler
 * 
 * Authenticates users by verifying email and password.
 * Creates session on successful authentication.
 * Uses password_verify() for secure password checking.
 * 
 * @package Flarify
 * @author Flarify Team
 */

require "../config.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['userpassword'])) {
        $_SESSION['user'] = $user;
        header("Location:../index.php?page=dashboard");
    } else {
        error_log("Login failed for email: $email");
        header("Location:../index.php?page=login");
    }
}
?>