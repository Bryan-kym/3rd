<?php
session_start();
header('Content-Type: application/json');
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    // Check if OTP exists and is valid
    $stmt = $conn->prepare("SELECT id, expires_at FROM otp_verification WHERE email = ? AND otp = ? AND status = 'active'");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($row = $result->fetch_assoc()) {
        $expires_at = strtotime($row['expires_at']);
        if ($expires_at < time()) {
            echo json_encode(["status" => "error", "message" => "OTP expired. Please request a new one."]);
        } else {
            // Mark OTP as inactive
            $stmt = $conn->prepare("UPDATE otp_verification SET status = 'inactive' WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();

            echo json_encode(["status" => "success", "message" => "OTP verified successfully"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid OTP. Try again."]);
    }
    exit;
}
echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
?>
