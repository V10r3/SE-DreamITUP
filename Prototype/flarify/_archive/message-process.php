<?php
include 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $to_id = 1;  // Hardcoded; make dynamic
    $stmt = $conn->prepare("INSERT INTO messages (from_id, to_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $_SESSION['user_id'], $to_id, $message);
    $stmt->execute();
    header("Location: messages.php");
}
?>