<?php
class SessionManager
{
    private $conn;
    private $warningThreshold = 300; // 5 minutes in seconds
    private $sessionDuration = 1800; // 30 minutes in seconds

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Update session expiration time
     */
    public function extendSession($token)
    {
        try {
            $newExpiration = date('Y-m-d H:i:s', time() + $this->sessionDuration);

            $stmt = $this->conn->prepare("
                UPDATE ext_sessions 
                SET expires_at = ?, last_accessed = NOW() 
                WHERE token = ?
            ");
            $stmt->bind_param("ss", $newExpiration, $token);
            $stmt->execute();
            $stmt->close();

            return $newExpiration;
        } catch (Exception $e) {
            error_log("Session extension failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if session is about to expire
     */
    public function checkSessionWarning($token)
    {
        error_log("CHECK SESSION WARNING CALLED - Token: $token");
        try {
            $stmt = $this->conn->prepare("
                SELECT expires_at 
                FROM ext_sessions 
                WHERE token = ?
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $expiresAt = strtotime($row['expires_at']);
                $remaining = $expiresAt - time();

                error_log("Current time: " . date('Y-m-d H:i:s'));
                error_log("Expires at: " . $row['expires_at']);
                error_log("Remaining seconds: $remaining");
                error_log("Warning threshold: {$this->warningThreshold}");

                return $remaining <= $this->warningThreshold;
            }
            error_log("No session found for token");
            return false;
        } catch (Exception $e) {
            error_log("Session check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get remaining session time in seconds
     */
    public function getRemainingSessionTime($token)
    {
        try {
            $stmt = $this->conn->prepare("
            SELECT TIMESTAMPDIFF(SECOND, NOW(), expires_at) AS remaining 
            FROM ext_sessions 
            WHERE token = ?
        ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                return (int)$row['remaining'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Remaining time check failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Destroy session (for logout)
     */
    public function destroySession($token)
{
    try {
        // First verify the token exists
        $checkStmt = $this->conn->prepare("SELECT token FROM ext_sessions WHERE token = ?");
        $checkStmt->bind_param("s", $token);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        $checkStmt->close();
        
        if (!$exists) {
            error_log("Session not found for token: " . substr($token, 0, 6) . "...");
            return false;
        }

        // Then delete it
        $deleteStmt = $this->conn->prepare("DELETE FROM ext_sessions WHERE token = ?");
        $deleteStmt->bind_param("s", $token);
        $success = $deleteStmt->execute();
        $affectedRows = $deleteStmt->affected_rows;
        $deleteStmt->close();
        
        error_log("Session deletion attempted. Token: " . substr($token, 0, 6) . "... Affected rows: $affectedRows");
        
        return $success && ($affectedRows > 0);
    } catch (Exception $e) {
        error_log("Session destruction failed: " . $e->getMessage());
        return false;
    }
}

    public function updateSessionExpiry($token, $expiryTime = null)
    {
        if (empty($token)) {
            throw new Exception("Token cannot be empty");
        }

        $currentTime = $expiryTime ?? date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare("UPDATE ext_sessions 
                                     SET expires_at = ? 
                                     WHERE token = ?");
        $stmt->bind_param("ss", $currentTime, $token);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update session expiry: " . $stmt->error);
        }

        $stmt->close();
        return true;
    }
}
