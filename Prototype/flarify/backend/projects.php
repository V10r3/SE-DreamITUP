<?php
require "../config.php";
session_start();
if (!isset($_SESSION['user'])) { header("Location: ../index.php?page=login"); exit; }

$title = trim($_POST['title'] ?? '');
$desc  = trim($_POST['description'] ?? '');
$price = $_POST['price'] !== '' ? (float)$_POST['price'] : 0.00;
$demo  = isset($_POST['demo_flag']) ? 1 : 0;
$pre   = isset($_POST['preorder']) ? 1 : 0;
$hide  = isset($_POST['hide']) ? 1 : 0;

if (!$title) {
  $_SESSION['flash'] = "Title is required.";
  header("Location: ../index.php?page=upload"); exit;
}

if (!is_dir("../uploads")) { mkdir("../uploads", 0775, true); }
$storedPath = '';
if (!empty($_FILES['file']['name'])) {
  $fname = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['file']['name']);
  $path = "../uploads/" . $fname;
  if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
    $_SESSION['flash'] = "File upload failed.";
    header("Location: ../index.php?page=upload"); exit;
  }
  $storedPath = $path;
}

$stmt = $pdo->prepare("
  INSERT INTO projects (developer_id,title,description,price,file_path,demo_flag,hidden,preorder)
  VALUES (?,?,?,?,?,?,?,?)
");
$stmt->execute([$_SESSION['user']['id'], $title, $desc, $price, $storedPath, $demo, $hide, $pre]);

$_SESSION['flash'] = "Project uploaded.";
header("Location: ../index.php?page=dashboard");