<?php
$permissionId = $_SERVER['REVIEW_PERMISSION'];
$roles = getAllRoles($conn, $permissionId);

if ($roles && count($roles) > 0) {
    foreach ($roles as $role) {
        $roleId = $role['role_id'];
        $recipients = getEmailsByRole($conn, $roleId);
        if ($recipients && count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                $subject_ = "Third-Party New Request";
                $message_ = "Kindly review the Request with Tracking id: " . $trackingID . "  ASAP";
                $recipientemail_ = $recipient['email'];

                include 'send_email.php';
            }
        }
    }
    exit;
}


function getAllRoles($conn, $permissionId)
{
    // Prepare a query to fetch email and names for all active users with the given role
    $stmt = $conn->prepare("SELECT role_id FROM role_permissions WHERE status = 1 AND permission_id = ?");
    if (!$stmt) {
        return false; // Handle error appropriately
    }
    $stmt->bind_param("i", $permissionId);
    $stmt->execute();
    $result = $stmt->get_result();

    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = [
            'role_id' => $row['role_id']
        ];
    }
    $stmt->close();

    return $roles;
}

function getEmailsByRole($conn, $roleId)
{
    // Prepare a query to fetch email and names for all active users with the given role
    $stmt = $conn->prepare("SELECT email FROM users WHERE status = 'Active' AND role = ?");
    if (!$stmt) {
        return false; // Handle error appropriately
    }
    $stmt->bind_param("i", $roleId);
    $stmt->execute();
    $result = $stmt->get_result();

    $recipients = [];
    while ($row = $result->fetch_assoc()) {
        $recipients[] = [
            'email' => $row['email']
        ];
    }
    $stmt->close();

    return $recipients;
}
