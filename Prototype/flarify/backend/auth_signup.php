<?php
/**
 * User Registration Handler
 * 
 * Creates new user accounts with secure password hashing.
 * Validates password confirmation and creates session on success.
 * Uses Argon2ID algorithm for password hashing (strongest available).
 * 
 * @package Flarify
 * @author Flarify Team
 */

require "../config.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $role = $_POST['role'];

    // Validate password confirmation
    if ($password !== $confirm) {
        error_log("Signup failed: Passwords do not match for user: $email");
        header("Location:../index.php?page=signup");
        exit;
    }

    // Hash password using Argon2ID (most secure algorithm)
    $hash = password_hash($password, PASSWORD_ARGON2ID);

    $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
    $stmt->execute([$name,$email,$hash,$role]);

    $_SESSION['user'] = [
        'id' => $pdo->lastInsertId(),
        'name' => $name,
        'email' => $email,
        'role' => $role
    ];
    header("Location:../index.php?page=dashboard");
}
?>