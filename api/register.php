<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

try {
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM ext_users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
    
    // Create user (initially inactive until OTP verification)
    $stmt = $conn->prepare("INSERT INTO ext_users (email, password_hash, is_active) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $data['email'], $passwordHash);
    $stmt->execute();
    $userId = $stmt->insert_id;

    // Generate OTP
    $otp = rand(100000, 999999); // 6-digit OTP
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Store OTP in database
    $stmt = $conn->prepare("INSERT INTO ext_otps (user_id, code, expires_at, type) VALUES (?, ?, ?, 'registration')");
    $stmt->bind_param("iss", $userId, $otp, $expiresAt);
    $stmt->execute();

    // Send OTP via email
    $subject_ = "Your Account Verification Code";
    $message_ = "Your verification code is: $otp\n\nThis code will expire in 10 minutes.";
    $recipientemail_ = $data['email'];
    require_once '../send_email.php';

    if (strpos($emailSentStatus, 'success') === false) {
        // First delete the OTP record
        $stmt = $conn->prepare("DELETE FROM ext_otps WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Then delete the user
        $stmt = $conn->prepare("DELETE FROM ext_users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send verification email']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'userId' => $userId,
        'message' => 'Verification OTP sent to your email'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}