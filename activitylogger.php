<?php
require_once __DIR__ . '/config.php';

class ActivityLogger {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Log user activity with additional context
     */
    public function logActivity($userId, $action, $description, $extraData = null) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $this->conn->prepare("
            INSERT INTO user_activity 
            (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", 
            $userId,
            $action,
            $description,
            $ipAddress,
            $userAgent
        );
        
        if (!$stmt->execute()) {
            error_log("Failed to log activity: " . $stmt->error);
        }
        
        // Optionally log extended data to a separate table or file
        if ($extraData !== null) {
            $this->logExtendedData($userId, $action, $extraData);
        }
    }
    
    /**
     * Optional method for logging detailed data
     */
    private function logExtendedData($userId, $action, $data) {
        $logFile = __DIR__ . '/../logs/user_activity.log';
        $logData = json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'action' => $action,
            'data' => $data
        ]);
        
        file_put_contents($logFile, $logData . PHP_EOL, FILE_APPEND);
    }
    
    /**
     * Get recent activities for a user
     */
    public function getUserActivities($userId, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT action, description, created_at
            FROM user_activity
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $row['time_ago'] = $this->formatTimeAgo($row['created_at']);
            $activities[] = $row;
        }
        
        return $activities;
    }
    
    /**
     * Format time for display
     */
    private function formatTimeAgo($datetime) {
        $now = new DateTime();
        $then = new DateTime($datetime);
        $diff = $now->diff($then);
        
        if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'Just now';
    }
}
?>