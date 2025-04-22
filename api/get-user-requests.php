<?php
header('Content-Type: application/json');
require_once '../auth.php';
require_once '../config.php';

try {
    $userId = authenticate();
    
    $stmt = $conn->prepare("
        SELECT 
            r.id,
            r.tracking_id as tracking_number,
            r.date_requested as request_date,
            r.request_status,
            r.description as data_description,
            r.specific_fields,
            r.period_from,
            r.period_to,
            r.request_purpose,
            req.fullnames as requester_name,
            req.email as requester_email,
            req.phone_number as requester_phone,
            req.requester_type as category,
            req.kra_pin,
            req.requester_affiliation_name,
            req.requester_affiliation_email,
            req.requester_affiliation_phone
        FROM requests r
        JOIN requestors req ON r.requested_by = req.id
        WHERE req.email = (SELECT email FROM ext_users WHERE id = ?)
        ORDER BY r.date_requested DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates
        $requestDate = new DateTime($row['request_date']);
        $periodFrom = $row['period_from'] ? (new DateTime($row['period_from']))->format('M d, Y') : null;
        $periodTo = $row['period_to'] ? (new DateTime($row['period_to']))->format('M d, Y') : null;
        
        // Simplify request status
        $originalStatus = $row['request_status'];
        $simplifiedStatus = $originalStatus; // default to original
        
        if ($originalStatus === 'resolved') {
            $simplifiedStatus = 'resolved';
        } elseif ($originalStatus === 'rejected') {
            $simplifiedStatus = 'rejected';
        } elseif (in_array($originalStatus, ['pending', 'requested' , 'resubmitted'])) {
            $simplifiedStatus = 'pending';
        }elseif (in_array($originalStatus, ['approved', 'reviewed', 'assigned'])) {
            $simplifiedStatus = 'in-progress';
        }
        
        $requests[] = [
            'id' => $row['id'],
            'tracking_number' => $row['tracking_number'],
            'request_date' => $requestDate->format('M d, Y'),
            'datetime' => $requestDate->format('Y-m-d H:i:s'),
            'request_status' => $simplifiedStatus,
            'original_status' => $originalStatus, // Keep original if needed for reference
            'category' => $row['category'],
            'data_description' => $row['data_description'],
            'specific_fields' => $row['specific_fields'],
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'request_purpose' => $row['request_purpose'],
            'requester' => [
                'name' => $row['requester_name'],
                'email' => $row['requester_email'],
                'phone' => $row['requester_phone'],
                'kra_pin' => $row['kra_pin'],
                'affiliation' => [
                    'name' => $row['requester_affiliation_name'],
                    'email' => $row['requester_affiliation_email'],
                    'phone' => $row['requester_affiliation_phone']
                ]
            ]
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $requests]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}