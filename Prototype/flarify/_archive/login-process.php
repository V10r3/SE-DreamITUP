<?php
include 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT id, userpassword, userrole FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['userpassword'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['userrole'] = $user['userrole'];
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
        } else {
            header("Location: index.php?error=invalid_password");
        }
    } else {
        header("Location: index.php?error=user_not_found");
    }
}
?>