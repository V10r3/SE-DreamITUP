<?php
$host = 'localhost';
$db = 'flarify';
$user = 'root';
$pass = 'root';  // Change for production
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
session_start();
?>