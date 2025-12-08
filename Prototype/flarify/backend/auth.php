<?php
require "../config.php";
session_start();
$action = $_GET['action'] ?? '';

if ($action === 'signup') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm'] ?? '';
  $role = $_POST['role'] ?? 'developer';

  if (!$name || !$email || !$password || $password !== $confirm) {
    $_SESSION['flash'] = "Fill all fields and ensure passwords match.";
    header("Location: ../index.php?page=signup"); exit;
  }

  $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)");
  try {
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT), $role]);
    $_SESSION['flash'] = "Account created. Log in now.";
    header("Location: ../index.php?page=login");
  } catch (PDOException $e) {
    $_SESSION['flash'] = strpos($e->getMessage(), 'Duplicate') !== false
      ? "Email already exists." : "Server error.";
    header("Location: ../index.php?page=signup");
  }
  exit;
}

if ($action === 'login') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user'] = $user;
    header("Location: ../index.php?page=dashboard"); exit;
  }
  $_SESSION['flash'] = "Invalid credentials.";
  header("Location: ../index.php?page=login"); exit;
}

if ($action === 'logout') {
  session_destroy();
  header("Location: ../index.php?page=login"); exit;
}

header("Location: ../index.php?page=login");