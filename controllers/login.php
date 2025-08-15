<?php
session_start(); // Always at the very top

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit;
}

// Decode incoming JSON payload
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Email and password are required"]);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

//connect to database
require_once("../repositories/db_connect.php"); 

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE u_email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists
    if (!$user) {
        http_response_code(401);
        echo json_encode(["message" => "User not found"]);
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["message" => "Incorrect password"]);
        exit;
    }

    // Login successful: Generate token
    $token = bin2hex(random_bytes(16)); // 32-character token

    // Store user data and token in session
    $_SESSION['user'] = [
        'id' => $user['u_id'],
        'u_name' => $user['u_name'],   
        'u_email' => $user['u_email'],
        'u_type' => $user['u_type'],
        'u_points' => $user['u_points'],
        'token' => $token,
        'last_activity' => time()
    ];
    

    // Send response
    echo json_encode([
        "message" => "Login successful",
        "token" => $token,
        "user" => [
            "id" => $user['u_id'],
            "name" => $user['u_name'],
            "email" => $user['u_email'],
            "type" => $user['u_type'],
            "points" => $user['u_points']
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>
