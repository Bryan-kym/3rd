<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

try {
    // Check if session exists and has authToken
    if (!isset($_SESSION['authToken'])) {
        throw new Exception("No authentication token found in session");
    }

    // Get the token (sanitize for output)
    $token = $_SESSION['authToken'];
    
    // For security, we'll only show the first and last few characters in production
    $displayToken = (strlen($token) > 12) 
        ? substr($token, 0, 6) . '...' . substr($token, -6)
        : $token;

    // Return the token (in production, you might want to return only partial token)
    echo json_encode([
        'success' => true,
        'token' => $token,  // In production, consider using $displayToken instead
        'partial_token' => $displayToken,
        'token_length' => strlen($token),
        'message' => 'Token retrieved successfully'
    ]);

} catch (Exception $e) {
    http_response_code(401); // Unauthorized
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'token' => null
    ]);
}