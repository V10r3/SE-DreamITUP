<?php
include 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    if ($_POST['password'] === $_POST['confirm_password']) {
        $hashed_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, username, userpassword) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $username, $hashed_pass);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            header("Location: role-selection.php");
        } else {
            header("Location: signup.php?error=signup_failed");
        }
    } else {
        header("Location: signup.php?error=password_mismatch");
    }
}
?>