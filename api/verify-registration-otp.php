<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($data['userId']) || empty($data['otp'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID and OTP are required']);
    exit;
}

try {
    // Verify OTP - removed reference to created_at
    $stmt = $conn->prepare("SELECT * FROM ext_otps 
                          WHERE user_id = ? AND code = ? AND is_used = 0 AND expires_at > NOW() AND type = 'registration'
                          ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("is", $data['userId'], $data['otp']);
    $stmt->execute();
    $result = $stmt->get_result();
    $otpRecord = $result->fetch_assoc();

    if (!$otpRecord) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        exit;
    }

    // Mark OTP as used
    $stmt = $conn->prepare("UPDATE ext_otps SET is_used = 1 WHERE id = ?");
    $stmt->bind_param("i", $otpRecord['id']);
    $stmt->execute();

    // Activate user account
    $stmt = $conn->prepare("UPDATE ext_users SET is_active = 1 WHERE id = ?");
    $stmt->bind_param("i", $data['userId']);
    $stmt->execute();

    // Generate auth token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Create session
    $stmt = $conn->prepare("INSERT INTO ext_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $data['userId'], $token, $expiresAt);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'token' => $token,
        'message' => 'Account activated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}