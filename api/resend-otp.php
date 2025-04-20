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
    // Generate new OTP
    $otp = rand(100000, 999999); // 6-digit OTP
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Store OTP in database
    $stmt = $conn->prepare("INSERT INTO ext_otps (user_id, code, expires_at, type) VALUES (?, ?, ?, 'login')");
    $stmt->bind_param("iss", $data['userId'], $otp, $expiresAt);
    $stmt->execute();

    // Send OTP via email - using correct variable names with underscores
    $subject_ = "Your New Verification OTP";
    $message_ = "Your new verification code is: $otp\n\nThis code will expire in 10 minutes.";
    $recipientemail_ = $data['email'];
    
    // Include the email sending script
    require_once '../send_email.php';
    
    // Check email status - using $emailSentStatus instead of $emailSent
    if (strpos($emailSentStatus, 'success') === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to resend OTP',
            'error' => $emailSentStatus // Optional: include the actual error for debugging
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'New OTP sent successfully'
    ]);

} catch (Exception $e) { // Changed from PDOException to Exception for MySQLi
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}