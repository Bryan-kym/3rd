<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$requiredFields = ['surname', 'otherNames', 'email', 'phone', 'password'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
        exit;
    }
}

try {
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Validate phone number (basic check for international format)
    if (!preg_match('/^\+[1-9]\d{1,14}$/', $data['phone'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid phone number format. Please include country code']);
        exit;
    }

    // Validate KRA PIN format if provided
    // if (!empty($data['kraPin']) && !preg_match('/^[A-Za-z]\d{9}[A-Za-z]$/', $data['kraPin'])) {
    //     http_response_code(400);
    //     echo json_encode(['success' => false, 'message' => 'Invalid KRA PIN format (should be A123456789X format)']);
    //     exit;
    // }

    // Validate password strength
    if (!preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/', $data['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with 1 number and 1 special character']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM ext_users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    // Check if phone number already exists
    $stmt = $conn->prepare("SELECT id FROM ext_users WHERE phone = ?");
    $stmt->bind_param("s", $data['phone']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Phone number already registered']);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
    
    // Create user (initially inactive until OTP verification)
    $stmt = $conn->prepare("INSERT INTO ext_users 
        (email, password_hash, surname, other_names, phone, is_active) 
        VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssss", 
        $data['email'], 
        $passwordHash,
        $data['surname'],
        $data['otherNames'],
        $data['phone']
    );
    $stmt->execute();
    $userId = $stmt->insert_id;

    // Generate OTP
    $otp = rand(100000, 999999); // 6-digit OTP
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Store OTP in database
    $stmt = $conn->prepare("INSERT INTO ext_otps (user_id, code, expires_at, type) VALUES (?, ?, ?, 'registration')");
    $stmt->bind_param("iss", $userId, $otp, $expiresAt);
    $stmt->execute();

    // Prepare email content
    $subject_ = "Your Account Verification Code";
    $message_ = "Dear " . $data['surname'] . ",\n\n";
    $message_ .= "Your verification code is: $otp\n\n";
    $message_ .= "This code will expire in 10 minutes.\n\n";
    $message_ .= "Thank you for registering with us!";
    $recipientemail_ = $data['email'];

    // Send OTP via email
    require_once '../send_email.php';

    if (strpos($emailSentStatus, 'success') === true) {
        // Clean up if email fails to send
        $conn->begin_transaction();
        
        // Delete OTP record
        $stmt = $conn->prepare("DELETE FROM ext_otps WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Delete user record
        $stmt = $conn->prepare("DELETE FROM ext_users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $conn->commit();
        
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
    // Rollback any transactions if they were started
    if (isset($conn) && method_exists($conn, 'rollback')) {
        $conn->rollback();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration error: ' . $e->getMessage()]);
}