<?php
session_start();

// Excluded pages
$excluded = ['login.html', 'register.html'];

// Get current script name
$current_page = basename($_SERVER['PHP_SELF']);

if (!in_array($current_page, $excluded)) {
    // Check if session is set
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['token'])) {
        header("Location: login.html");
        exit;
    }


    if (time() - $_SESSION['user']['last_activity'] > 3600) {
        session_unset();
        session_destroy();
        header("Location: login.html?timeout=1");
        exit;
    }

    // Update activity time
    $_SESSION['user']['last_activity'] = time();
}
?>
