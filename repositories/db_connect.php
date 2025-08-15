<?php
// db_connect.php

// Database credentials
$host = '127.0.0.1';
$port = 3307;
$dbname = 'driveline';
$user = 'root';
$pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If error, show it and stop execution
    die(json_encode(["message" => "Database connection failed: " . $e->getMessage()]));
}
?>
