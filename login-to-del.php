<?php
header('Content-Type: application/json');
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Check user credentials
$stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch();

if (!$user || !password_verify($data['password'], $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit;
}

// Generate and send OTP
$otp = rand(100000, 999999);
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$stmt = $pdo->prepare("INSERT INTO otps (user_id, code, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$user['id'], $otp, $expiresAt]);

// In a real app, send OTP via SMS/Email
// sendOtpToUser($user['phone'] or $data['email'], $otp);

echo json_encode([
    'success' => true,
    'userId' => $user['id'],
    'message' => 'OTP sent to your registered phone/email'
]);