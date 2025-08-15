<?php
session_start();
require_once("../repositories/db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Validate input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['review_id']) || !isset($_POST['car_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Bad request"]);
    exit;
}

$reviewId = (int)$_POST['review_id'];
$carId = (int)$_POST['car_id'];
$userId = $_SESSION['user']['id'];

try {
    // First verify the review belongs to the user
    $stmt = $pdo->prepare("SELECT u_id FROM reviews WHERE r_id = ?");
    $stmt->execute([$reviewId]);
    $review = $stmt->fetch();
    
    if (!$review || $review['u_id'] != $userId) {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden - not your review"]);
        exit;
    }
    
    // Delete the review
    $stmt = $pdo->prepare("CALL DeleteReview(?, ?)");
    $stmt->execute([$carId, $userId]);
    
    // Redirect back to car details
    header("Location: car_details.php?c_id=" . $carId);
    exit;
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}