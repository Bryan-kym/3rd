<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

class ProfileFunctions {
    private $conn;
    private $userId;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
        
        try {
            $this->userId = authenticate();
        } catch (Exception $e) {
            throw new Exception("Authentication failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch user profile data
     */
    public function getUserData() {
        $stmt = $this->conn->prepare("
            SELECT 
                id, email, surname, other_names, phone, 
                created_at, avatar_path, two_factor_enabled,
                kra_pin, is_verified, is_active
            FROM ext_users
            WHERE id = ?
        ");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }
        
        $userData = $result->fetch_assoc();
        
        // Split other_names into first and second names
        $nameParts = explode(' ', $userData['other_names'], 2);
        $userData['first_name'] = $nameParts[0] ?? '';
        $userData['second_name'] = $nameParts[1] ?? '';
        
        // Get recent activity
        $userData['activity'] = $this->getUserActivity();
        
        return $userData;
    }

    /**
     * Get user activity log
     */
    private function getUserActivity() {
        $stmt = $this->conn->prepare("
            SELECT 
                action as title, 
                description, 
                created_at,
                CASE 
                    WHEN action LIKE '%login%' THEN 'fa-sign-in-alt'
                    WHEN action LIKE '%update%' THEN 'fa-user-edit'
                    WHEN action LIKE '%password%' THEN 'fa-key'
                    WHEN action LIKE '%avatar%' THEN 'fa-camera'
                    WHEN action LIKE '%two%factor%' THEN 'fa-shield-alt'
                    ELSE 'fa-history'
                END as icon
            FROM user_activity
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $row['time'] = $this->formatTime($row['created_at']);
            $activities[] = $row;
        }
        
        return $activities;
    }

    /**
     * Update personal information
     */
    public function updatePersonalInfo($data) {
        // Combine first and second names into other_names
        $otherNames = trim($data['first_name'] . ' ' . ($data['second_name'] ?? ''));
        
        $stmt = $this->conn->prepare("
            UPDATE ext_users
            SET 
                surname = ?,
                other_names = ?,
                phone = ?,
                kra_pin = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", 
            $data['last_name'],
            $otherNames,
            $data['phone'],
            $data['kra_pin'],
            $this->userId
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update personal info: " . $stmt->error);
        }
        
        // Log activity
        $this->logActivity("Profile update", "Updated personal information");
        
        return true;
    }

    /**
     * Change user password
     */
    public function changePassword($currentPassword, $newPassword) {
        // Verify current password
        $stmt = $this->conn->prepare("SELECT password_hash FROM ext_users WHERE id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!password_verify($currentPassword, $user['password_hash'])) {
            throw new Exception("Current password is incorrect");
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE ext_users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $this->userId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update password: " . $stmt->error);
        }
        
        // Log activity
        $this->logActivity("Password change", "Changed account password");
        
        return true;
    }

    /**
     * Update profile picture
     */
    public function updateAvatar($file) {
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        // Validate file
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $validExtensions)) {
            throw new Exception("Invalid file type. Only JPG, PNG, GIF are allowed.");
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception("File size exceeds 2MB limit");
        }
        
        // Generate unique filename
        $newFilename = 'avatar_' . $this->userId . '_' . time() . '.' . $fileExt;
        $uploadPath = 'assets/uploads/avatars/' . $newFilename;
        
        // Create directory if not exists
        if (!file_exists('assets/uploads/avatars')) {
            mkdir('assets/uploads/avatars', 0777, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception("Failed to upload file");
        }
        
        // Update database
        $stmt = $this->conn->prepare("UPDATE ext_users SET avatar_path = ? WHERE id = ?");
        $stmt->bind_param("si", $uploadPath, $this->userId);
        
        if (!$stmt->execute()) {
            unlink($uploadPath); // Clean up if DB update fails
            throw new Exception("Failed to update avatar: " . $stmt->error);
        }
        
        // Log activity
        $this->logActivity("Avatar update", "Changed profile picture");
        
        return $uploadPath;
    }

    /**
     * Toggle two-factor authentication
     */
    public function toggleTwoFactor($enable) {
        $stmt = $this->conn->prepare("UPDATE ext_users SET two_factor_enabled = ? WHERE id = ?");
        $stmt->bind_param("ii", $enable, $this->userId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update two-factor setting: " . $stmt->error);
        }
        
        // Log activity
        $action = $enable ? "Enabled" : "Disabled";
        $this->logActivity("Security update", "{$action} two-factor authentication");
        
        return true;
    }

    /**
     * Log user activity
     */
    private function logActivity($action, $description) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $this->conn->prepare("
            INSERT INTO user_activity 
            (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", 
            $this->userId,
            $action,
            $description,
            $ipAddress,
            $userAgent
        );
        
        if (!$stmt->execute()) {
            error_log("Failed to log activity: " . $stmt->error);
        }
    }

    /**
     * Format time for display
     */
    private function formatTime($datetime) {
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