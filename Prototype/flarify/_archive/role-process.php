<?php
include 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $role = $_POST['role'];
    $stmt = $conn->prepare("UPDATE users SET userrole = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $_SESSION['user_id']);
    $stmt->execute();
    $_SESSION['userrole'] = $role;
    header("Location: dashboard.php");
}
?>