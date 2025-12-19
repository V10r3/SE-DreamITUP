<?php include 'includes/config.php'; if (!isset($_SESSION['user_id']) || $_SESSION['userrole'] !== 'Developer') header("Location: dashboard.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Flarify - Upload</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<header><!-- Same --></header>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <h1>Uploads</h1>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="File name" required>
        <input type="file" name="game_file" required>
        <input type="text" name="description" placeholder="Description" required>
        <input type="number" name="price" placeholder="Set a different price for this file" step="0.01">
        <label><input type="checkbox" name="demo"> This file is a demo and can be downloaded for free</label>
        <label><input type="checkbox" name="preorder"> This file is a pre-order placeholder</label>
        <label><input type="checkbox" name="hide"> Hide this file and prevent it from being downloaded</label>
        <button type="submit" style="background: red;">Upload file</button>
    </form>
    <p>TIP: Use butler to upload game files only uploads what's changed, generates patches for the itch.io app, and you can automate it. Get started!</p>
    <a href="#">Add External file ?</a>
    <p>File size limit: 1 GB. Contact us if you need more space</p>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'] ?? 0.00;
    $demo = isset($_POST['demo']) ? 1 : 0;
    // Handle file upload
    $target_dir = "uploads/";
    $image = basename($_FILES["game_file"]["name"]);
    move_uploaded_file($_FILES["game_file"]["tmp_name"], $target_dir . $image);
    $stmt = $conn->prepare("INSERT INTO games (title, description, developer_id, price, image, demo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidsi", $title, $description, $_SESSION['user_id'], $price, $image, $demo);
    $stmt->execute();
    header("Location: dashboard.php");
}
?>
<script src="js/script.js"></script>
</body>
</html>