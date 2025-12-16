<?php
session_unset();   // remove all session variables
session_destroy(); // destroy the session
header("Location: index.php?page=login");
exit;
?>