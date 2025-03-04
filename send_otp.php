<?php
session_start();
header('Content-Type: application/json');
include 'config.php'; // Include DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;

    // Set expiration time (5 minutes)
    $expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // Insert OTP into database
    $stmt = $conn->prepare("INSERT INTO otp_verification (email, otp, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $otp, $expires_at);
    $stmt->execute();
    $stmt->close();

    // Send email
    $subject_ = "Your OTP Code";
    $message_ = "Your OTP code is: $otp. It is valid for 5 minutes.";
    $recipientemail_ = $email;
    include 'send_email.php';

    echo json_encode(["status" => "success", "message" => "OTP sent successfully"]);
    exit;
}
echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
?>
