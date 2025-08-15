<?php
session_start();
session_unset();
session_destroy();
header("Location: ../pages/login.html"); // Adjust path based on your file structure
exit;
?>
