<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($data['otp']) || empty($_SESSION['otp_verification_user'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'OTP verification failed']);
    exit;
}

try {
    // Verify OTP
    $stmt = $conn->prepare("SELECT * FROM ext_otps 
                          WHERE user_id = ? AND code = ? AND is_used = 0 AND expires_at > NOW() AND type = 'login'
                          ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("is", $_SESSION['otp_verification_user'], $data['otp']);
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

    // Generate auth token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Create session
    $stmt = $conn->prepare("INSERT INTO ext_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['otp_verification_user'], $token, $expiresAt);
    $stmt->execute();

    // Set session and client-side tokens
    $_SESSION['authToken'] = $token;
    $redirectUrl = $_SESSION['post_login_redirect'] ?? 'dashboard.php';
    
    // Clean up session variables
    unset($_SESSION['otp_verification_user']);
    unset($_SESSION['otp_created_at']);
    unset($_SESSION['post_login_redirect']);

    echo json_encode([
        'success' => true,
        'token' => $token,
        'redirect' => $redirectUrl,
        'message' => 'Login successful'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}









// <?php
// header('Content-Type: application/json');
// require_once '../config.php';

// $data = json_decode(file_get_contents('php://input'), true);

// // Validate input
// if (empty($data['userId']) || empty($data['otp'])) {
//     http_response_code(400);
//     echo json_encode(['success' => false, 'message' => 'User ID and OTP are required']);
//     exit;
// }

// try {
//     // Verify OTP - using 'id' column for ordering instead of 'created_at'
//     $stmt = $conn->prepare("SELECT * FROM ext_otps 
//                           WHERE user_id = ? AND code = ? AND is_used = 0 AND expires_at > NOW() AND type = 'login'
//                           ORDER BY id DESC LIMIT 1");
//     $stmt->bind_param("is", $data['userId'], $data['otp']);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $otpRecord = $result->fetch_assoc();

//     if (!$otpRecord) {
//         http_response_code(401);
//         echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
//         exit;
//     }

//     // Mark OTP as used
//     $stmt = $conn->prepare("UPDATE ext_otps SET is_used = 1 WHERE id = ?");
//     $stmt->bind_param("i", $otpRecord['id']);
//     $stmt->execute();

//     // Generate auth token
//     $token = bin2hex(random_bytes(32));
//     $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

//     // Create session
//     $stmt = $conn->prepare("INSERT INTO ext_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
//     $stmt->bind_param("iss", $data['userId'], $token, $expiresAt);
//     $stmt->execute();

//     echo json_encode([
//         'success' => true,
//         'token' => $token,
//         'message' => 'Login successful'
//     ]);

// } catch (Exception $e) {
//     http_response_code(500);
//     echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
// }