<?php
include 'header.php';
require_once 'auth.php';
require_once 'config.php';

try {
    // Authenticate user
    $userId = authenticate();
    
    // Get request ID from URL
    $requestId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$requestId) {
        throw new Exception('Invalid request ID');
    }

    // Fetch request details
    $stmt = $conn->prepare("
        SELECT 
            r.id, r.tracking_id, r.request_status as status, r.description, r.specific_fields,
            r.period_from, r.period_to, r.request_purpose, r.date_requested,
            req.fullnames, req.email, req.phone_number, req.requester_type,
            req.kra_pin, req.requester_affiliation_name
        FROM requests r
        JOIN requestors req ON r.requested_by = req.id
        WHERE r.id = ? AND req.email = (SELECT email FROM ext_users WHERE id = ?)
    ");
    $stmt->bind_param("ii", $requestId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if (!$request) {
        throw new Exception('Request not found');
    }

    // Format dates
    $request['date_requested'] = date('M d, Y', strtotime($request['date_requested']));
    $request['period_from'] = $request['period_from'] ? date('M d, Y', strtotime($request['period_from'])) : 'N/A';
    $request['period_to'] = $request['period_to'] ? date('M d, Y', strtotime($request['period_to'])) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details</title>
    <style>
        .detail-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px;
        }
        .detail-section {
            margin-bottom: 2rem;
        }
        .detail-row {
            display: flex;
            margin-bottom: 1rem;
        }
        .detail-label {
            font-weight: bold;
            width: 200px;
        }
        .detail-value {
            flex: 1;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 2rem;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-reviewed { background: #cce5ff; color: #004085; }
        .status-resolved { background: #d4edda; color: #155724; }
        .status-assigned { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            transform: translateX(200%);
            transition: transform 0.3s ease, opacity 0.3s ease;
            z-index: 1000;
            color: white;
            display: flex;
            align-items: center;
            max-width: 400px;
            opacity: 0;
        }
        
        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .notification.success {
            background: #28a745;
            border-left: 5px solid #218838;
        }
        
        .notification.error {
            background: #dc3545;
            border-left: 5px solid #c82333;
        }
        
        .notification.info {
            background: #17a2b8;
            border-left: 5px solid #138496;
        }
        
        .notification.warning {
            background: #ffc107;
            border-left: 5px solid #e0a800;
            color: #212529;
        }
        
        .notification-icon {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .notification-close {
            margin-left: 15px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Enhanced Notification Element -->
    <div class="notification" id="notification">
        <span class="notification-icon" id="notificationIcon"></span>
        <span id="notificationMessage"></span>
        <span class="notification-close" id="notificationClose">&times;</span>
    </div>

    <?php if (isset($_SESSION['notification'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('<?php echo addslashes($_SESSION['notification']['message']); ?>', 
                           '<?php echo $_SESSION['notification']['type']; ?>');
        });
    </script>
    <?php 
        unset($_SESSION['notification']);
    endif; 
    ?>

    <div class="detail-container">
        <h1>Request Details <small>(<?php echo htmlspecialchars($request['tracking_id']); ?>)</small></h1>
        
        <div class="detail-section">
            <h3>Basic Information</h3>
            <div class="detail-row">
                <div class="detail-label">Request Date:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['date_requested']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                        <?php echo htmlspecialchars($request['status']); ?>
                    </span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Requester Type:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['requester_type']); ?></div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Request Details</h3>
            <div class="detail-row">
                <div class="detail-label">Data Description:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['description']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Specific Fields:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['specific_fields']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Period:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($request['period_from']); ?> to <?php echo htmlspecialchars($request['period_to']); ?>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Purpose:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['request_purpose']); ?></div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Requester Information</h3>
            <div class="detail-row">
                <div class="detail-label">Full Name:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['fullnames']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['email']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Phone:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['phone_number']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">KRA PIN:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['kra_pin']); ?></div>
            </div>
            <?php if ($request['requester_affiliation_name']): ?>
            <div class="detail-row">
                <div class="detail-label">Affiliation:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['requester_affiliation_name']); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <?php if (strtolower($request['status']) === 'pending'): ?>
                <a href="edit-request.php?id=<?php echo $requestId; ?>" class="btn btn-primary">Edit Request</a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script>
        // Enhanced notification function
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationIcon = document.getElementById('notificationIcon');
            const notificationMessage = document.getElementById('notificationMessage');
            
            // Clear previous classes
            notification.className = 'notification';
            notification.classList.add(type);
            
            // Set icon based on type
            const icons = {
                success: '✓',
                error: '✗',
                info: 'ℹ',
                warning: '⚠'
            };
            notificationIcon.textContent = icons[type] || '';
            
            // Set message
            notificationMessage.textContent = message;
            
            // Show notification
            notification.classList.add('show');
            
            // Auto-hide after 5 seconds
            const autoHide = setTimeout(() => {
                notification.classList.remove('show');
            }, 5000);
            
            // Manual close handler
            document.getElementById('notificationClose').onclick = function() {
                clearTimeout(autoHide);
                notification.classList.remove('show');
            };
            
            // Click anywhere to dismiss
            notification.onclick = function() {
                clearTimeout(autoHide);
                notification.classList.remove('show');
            };
        }

        // Check for URL parameters to show messages
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('message')) {
                showNotification(
                    decodeURIComponent(urlParams.get('message')),
                    urlParams.get('type') || 'info'
                );
                // Clean up URL
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
    </script>
</body>
</html>
<?php
} catch (Exception $e) {
    $_SESSION['notification'] = [
        'message' => $e->getMessage(),
        'type' => 'error'
    ];
    header('Location: dashboard.php');
    exit;
}

include 'footer.php';