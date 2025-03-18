<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once "vendor/autoload.php";

$mail_sender = $_SERVER['MAIL_SENDER'];
$emailSentStatus = '';

//connect to php mailer
$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPAuth = false;
$mail->SMTPAutoTLS = false;
$mail->SMTPDebug = 0; //no debug logs, to be used in prod
$mail->Host = "10.150.11.11";

$mail->SMTPSecure = "ssl";
$mail->Port = 25;
$mail->isHTML(true);
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->setfrom($mail_sender, "Third-party");

$mail->smtpConnect(
    array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    )
);


$email_to = $recipientemail_;
$subject = $subject_;
$message = $message_;



// Example: Loop over recipients and send an email using sendEmail() function

// $email_to = "Renhard.miyoma@kra.go.ke";
// $subject = "test";
// $message = "testing";

ob_start();
echo "$message<br><p>This email is auto-generated, kindly don't reply.</p>";

$body = ob_get_clean();

$mail->AddAddress($email_to);
$mail->Subject = $subject;
$mail->Body = $body;

if (!$mail->send()) {
    $error = $mail->ErrorInfo;
    $emailSentStatus = "failed: " . $error;
} else {
    $emailSentStatus = "success";
}

//echo $emailSentStatus;