<?php
header('Content-Type: application/json');
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$recipientemail_ = '';
$subject_ = '';
$message_ = '';

// Validate input
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id FROM ext_users WHERE email = ?");
$stmt->execute([$data['email']]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    exit;
}

// Create user
$passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO ext_users (email, password_hash, phone) VALUES (?, ?, ?)");
$stmt->execute([$data['email'], $passwordHash, $data['phone'] ?? null]);
$userId = $pdo->lastInsertId();

// Generate and send OTP
$otp = rand(100000, 999999); // 6-digit OTP
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$stmt = $pdo->prepare("INSERT INTO ext_otps (user_id, code, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$userId, $otp, $expiresAt]);

// In a real app, send OTP via SMS/Email
// sendOtpToUser($data['phone'] or $data['email'], $otp);

echo json_encode([
    'success' => true,
    'userId' => $userId,
    'message' => 'OTP sent to your phone/email'
]);