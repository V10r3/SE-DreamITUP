<?php
/**
 * Game Upload Handler
 * 
 * Processes new game uploads from developers.
 * Handles file upload and creates project database entry.
 * Restricted to developer role only.
 * 
 * @package Flarify
 * @author Flarify Team
 */

require "../config.php";
session_start();

// Authorization check: Only developers can upload games
if ($_SESSION['user']['role'] !== 'developer') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $demo_flag = isset($_POST['demo_flag']) ? 1 : 0;

    // Configure upload directory
    $targetDir = "../uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $filename = basename($_FILES["gamefile"]["name"]);
    $targetFile = $targetDir . time() . "_" . $filename;

    if (move_uploaded_file($_FILES["gamefile"]["tmp_name"], $targetFile)) {
        $stmt = $pdo->prepare("INSERT INTO projects (developer_id,title,description,price,demo_flag,file_path) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$_SESSION['user']['id'],$title,$description,$price,$demo_flag,$targetFile]);
        header("Location:../index.php?page=dashboard");
    } else {
        error_log("File upload failed for user: " . $_SESSION['user']['id']);
        header("Location:../index.php?page=upload");
    }
}
?>