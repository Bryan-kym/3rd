<?php
function sendOtp($email, $phone, $otp) {
    // Implement your actual email/SMS sending logic here
    // This is a placeholder implementation
    
    // For email (using PHP's mail function as example)
    $subject = 'Your Login OTP';
    $message = "Your OTP code is: $otp\nThis code will expire in 10 minutes.";
    $headers = 'From: no-reply@yourdomain.com';
    
    $emailSent = mail($email, $subject, $message, $headers);
    
    // For SMS, you would integrate with an SMS gateway API
    // $smsSent = sendSms($phone, "Your OTP code is: $otp");
    
    return $emailSent; // Return true only if sending was successful
}