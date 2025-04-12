<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';
require_once '../helpers/email_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

try {
    // Check user credentials
    $stmt = $conn->prepare("SELECT id, password_hash, email, phone, is_active FROM ext_users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if user exists
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    // Verify password
    if (!password_verify($data['password'], $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    // Check account status
    if (!$user['is_active']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Account is inactive. Please contact support.']);
        exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Store OTP in database
    $stmt = $conn->prepare("INSERT INTO ext_otps (user_id, code, expires_at, type) VALUES (?, ?, ?, 'login')");
    $stmt->bind_param("iss", $user['id'], $otp, $expiresAt);
    $stmt->execute();

    // Store user ID in session for OTP verification
    $_SESSION['otp_verification_user'] = $user['id'];
    $_SESSION['otp_created_at'] = time();

    // // Send OTP email
    $subject_ = "Your Login Verification Code";
    $message_ = "Your verification code is: $otp\n\nThis code will expire in 10 minutes.";
    $recipientemail_ = $user['email'];
    require_once '../send_email.php';

    if (strpos($emailSentStatus, 'success') === true) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP email']);
        exit;
    }

    // Return success response with redirect info if exists
    $response = [
        'success' => true,
        'userId' => $user['id'],
        'message' => 'OTP sent to your registered email'
    ];

    // Add redirect info if available (for non-API calls)
    if (isset($_SESSION['post_login_redirect'])) {
        $response['redirect'] = $_SESSION['post_login_redirect'];
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}