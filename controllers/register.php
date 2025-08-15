<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// connect to database
require_once("../repositories/db_connect.php");


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['fullName']) || !isset($data['email']) || !isset($data['password']) ||
    !isset($data['userType'])
) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields"]);
    exit;
}

$fullName   = trim($data['fullName']);
$email      = trim($data['email']);
$userType   = trim($data['userType']);
$profilePic = trim($data['profilePic'] ?? '');
$about      = trim($data['about'] ?? '');
$ownedCar   = trim($data['ownedCar'] ?? '');
$password   = $data['password'];
$u_points   = 0;

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$u_name = $fullName;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // CHECK IF EMAIL ALREADY EXISTS
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE u_email = :email");
    $checkStmt->execute([':email' => $email]);
    $emailExists = $checkStmt->fetchColumn();

    if ($emailExists > 0) {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Email already registered"]);
        exit;
    }

    // INSERT NEW USER
    $stmt = $pdo->prepare("INSERT INTO users (u_name, u_email, u_type, u_points, u_profile_pic, u_about, u_owned_car, password)
                           VALUES (:u_name, :u_email, :u_type, :u_points, :u_profile_pic, :u_about, :u_owned_car, :password)");

    $stmt->execute([
        ':u_name'        => $u_name,
        ':u_email'       => $email,
        ':u_type'        => $userType,
        ':u_points'      => $u_points,
        ':u_profile_pic' => $profilePic,
        ':u_about'       => $about,
        ':u_owned_car'   => $ownedCar,
        ':password'      => $hashedPassword
    ]);

    echo json_encode(["message" => "User registered successfully"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>
