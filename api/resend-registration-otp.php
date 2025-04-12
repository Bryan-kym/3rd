<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($data['userId']) || empty($data['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID and email are required']);
    exit;
}

try {
    // Check if user exists and is still inactive - using MySQLi properly
    $stmt = $conn->prepare("SELECT id FROM ext_users WHERE id = ? AND is_active = 0");
    $stmt->bind_param("i", $data['userId']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Account already activated or does not exist']);
        exit;
    }
    
    // Free the result
    $result->free();
    
    // Generate new OTP
    $otp = rand(100000, 999999); // 6-digit OTP
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Store OTP in database - using new statement
    $stmt2 = $conn->prepare("INSERT INTO ext_otps (user_id, code, expires_at, type) VALUES (?, ?, ?, 'registration')");
    $stmt2->bind_param("iss", $data['userId'], $otp, $expiresAt);
    $stmt2->execute();
    $stmt2->close();

    // Send OTP via email
    $subject_ = "Your New Verification OTP";
    $message_ = "Your new verification code is: $otp\n\nThis code will expire in 10 minutes.";
    $recipientemail_ = $data['email'];
    require_once '../send_email.php';

    if (strpos($emailSentStatus, 'success') === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to resend verification email']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'New OTP sent to your email'
    ]);

} catch (Exception $e) {  // Changed from PDOException to Exception
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}