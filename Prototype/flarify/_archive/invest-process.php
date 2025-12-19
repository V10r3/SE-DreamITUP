<?php
include 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['userrole'] === 'Investor') {
    $amount = $_POST['amount'];
    $game_id = $_POST['game_id'];
    $stmt = $conn->prepare("INSERT INTO investments (investor_id, game_id, amount) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $_SESSION['user_id'], $game_id, $amount);
    $stmt->execute();
    header("Location: game-detail.php?id=$game_id");
}
?>