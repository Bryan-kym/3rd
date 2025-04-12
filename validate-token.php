<?php
header('Content-Type: application/json');
require_once 'auth.php';

try {
    $userId = authenticate();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}