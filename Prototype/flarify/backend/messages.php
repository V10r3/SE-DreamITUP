<?php
require "../config.php";
session_start();
if (!isset($_SESSION['user'])) { header("Location: ../index.php?page=login"); exit; }

$receiver = (int)($_POST['receiver_id'] ?? 0);
$content  = trim($_POST['content'] ?? '');

if ($receiver <= 0 || !$content) {
  $_SESSION['flash'] = "Receiver and message are required.";
  header("Location: ../index.php?page=messages"); exit;
}

$stmt = $pdo->prepare("INSERT INTO messages (sender_id,receiver_id,content) VALUES (?,?,?)");
$stmt->execute([$_SESSION['user']['id'], $receiver, $content]);

$_SESSION['flash'] = "Message sent.";
header("Location: ../index.php?page=messages");