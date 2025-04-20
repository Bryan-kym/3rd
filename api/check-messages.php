<?php
header('Content-Type: application/json');
require_once '../auth.php';

session_start();

$message = null;
$type = 'info';

if (isset($_SESSION['auth_message'])) {
    $message = $_SESSION['auth_message'];
    $type = $_SESSION['auth_message_type'] ?? 'info';
    
    // Clear the message after retrieving
    unset($_SESSION['auth_message']);
    unset($_SESSION['auth_message_type']);
}

echo json_encode([
    'success' => true,
    'message' => $message,
    'type' => $type
]);