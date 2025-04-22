<?php
ob_start(); // Start output buffering
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

    // Fetch request details - adding review_comments and approver_comments to SELECT
    $stmt = $conn->prepare("
        SELECT 
            r.id, r.tracking_id, r.request_status as status, r.description, r.specific_fields,
            r.period_from, r.period_to, r.request_purpose, r.date_requested, r.rejected_at,
            r.review_comments, r.approver_comments,
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

    // Simplify the status for display
    if ($request['status'] === 'resolved') {
        $request['status'] = 'resolved';
    } elseif ($request['status'] === 'rejected') {
        $request['status'] = 'rejected';
    } elseif (in_array($request['status'], ['pending', 'requested', 'resubmitted'])) {
        $request['status'] = 'pending';
    } elseif (in_array($request['status'], ['approved', 'reviewed', 'assigned'])) {
        $request['status'] = 'in-progress';
    }

    // Format dates
    $request['date_requested'] = date('M d, Y', strtotime($request['date_requested']));
    $request['period_from'] = $request['period_from'] ? date('M d, Y', strtotime($request['period_from'])) : 'N/A';
    $request['period_to'] = $request['period_to'] ? date('M d, Y', strtotime($request['period_to'])) : 'N/A';
    
    // Get rejection reason if status is rejected
    $rejectionReason = '';
    if (strtolower($request['status']) === 'rejected') {
        // Check both possible comment fields
        if(strtolower($request['rejected_at']) === 'reviewer'){
            $rejectionReason = $request['review_comments'];
        } elseif(strtolower($request['rejected_at']) === 'approver'){
            $rejectionReason = $request['approver_comments'];
        } 
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details - KRA 3rd Party Data Request</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-red: #d9232e;
            --dark-red: #a51b24;
            --dark-black: #151515;
            --light-grey: #f8f9fa;
            --medium-grey: #e9ecef;
            --dark-grey: #6c757d;
            --border-radius: 0.5rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            background-color: var(--light-grey);
            font-family: 'Roboto', sans-serif;
            color: #212529;
        }

        .detail-container {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin: 2rem auto;
            max-width: 1200px;
            position: relative;
            overflow: hidden;
        }

        .detail-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-red), var(--dark-black));
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--medium-grey);
        }

        .detail-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark-black);
            margin: 0;
        }

        .detail-header small {
            font-size: 1rem;
            color: var(--dark-grey);
        }

        .detail-section {
            margin-bottom: 2.5rem;
            background: var(--light-grey);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .detail-section:hover {
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
        }

        .detail-section h3 {
            font-size: 1.2rem;
            color: var(--primary-red);
            margin-top: 0;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(217, 35, 46, 0.2);
        }

        .detail-row {
            display: flex;
            margin-bottom: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            width: 220px;
            color: var(--dark-black);
            display: flex;
            align-items: center;
        }

        .detail-label i {
            margin-right: 0.75rem;
            color: var(--primary-red);
            width: 20px;
            text-align: center;
        }

        .detail-value {
            flex: 1;
            color: var(--dark-grey);
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: var(--transition);
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-red);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--dark-red);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary-red);
            border: 1px solid var(--primary-red);
        }

        .btn-secondary:hover {
            background-color: rgba(217, 35, 46, 0.1);
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50rem;
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge i {
            font-size: 1rem;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .status-approved {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .status-rejected {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .status-reviewed {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .status-resolved {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .status-assigned {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .status-in-progress {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        /* Enhanced Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1.25rem 1.75rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1000;
            transform: translateX(200%);
            transition: var(--transition);
            opacity: 0;
            backdrop-filter: blur(5px);
            max-width: 400px;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification.success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(33, 136, 56, 0.9));
            color: white;
            border-left: 4px solid #1e7e34;
        }

        .notification.error {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9), rgba(200, 35, 51, 0.9));
            color: white;
            border-left: 4px solid #bd2130;
        }

        .notification.info {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.9), rgba(19, 132, 150, 0.9));
            color: white;
            border-left: 4px solid #0c5460;
        }

        .notification.warning {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.9), rgba(224, 168, 0, 0.9));
            color: #212529;
            border-left: 4px solid #d39e00;
        }

        .notification-icon {
            font-size: 1.25rem;
        }

        .notification-close {
            margin-left: 1rem;
            cursor: pointer;
            font-weight: bold;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .notification-close:hover {
            opacity: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .detail-container {
                padding: 2rem;
                margin: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .detail-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .detail-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .detail-row {
                flex-direction: column;
                gap: 0.5rem;
            }

            .detail-label {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .detail-container {
                padding: 1.25rem;
            }

            .detail-section {
                padding: 1rem;
            }
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
        <div class="detail-header">
            <h1>Request Details <small>(<?php echo htmlspecialchars($request['tracking_id']); ?>)</small></h1>
            <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                <i class="bi 
                <?php
                switch (strtolower($request['status'])) {
                    case 'pending':
                        echo 'bi-clock';
                        break;
                    case 'approved':
                        echo 'bi-check-circle';
                        break;
                    case 'rejected':
                        echo 'bi-x-circle';
                        break;
                    case 'reviewed':
                        echo 'bi-eye';
                        break;
                    case 'resolved':
                        echo 'bi-check-circle';
                        break;
                    case 'assigned':
                        echo 'bi-person';
                        break;
                    case 'in-progress':
                        echo 'bi-gear';
                        break;
                    default:
                        echo 'bi-info-circle';
                }
                ?>
            "></i>
                <?php echo htmlspecialchars($request['status']); ?>
            </span>
        </div>

        <div class="detail-section">
            <h3><i class="bi bi-card-heading"></i> Basic Information</h3>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-calendar"></i> Request Date:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['date_requested']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-person-badge"></i> Requester Type:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['requester_type']); ?></div>
            </div>
        </div>

        <div class="detail-section">
            <h3><i class="bi bi-file-text"></i> Request Details</h3>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-text-paragraph"></i> Data Description:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['description']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-list-check"></i> Specific Fields:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['specific_fields']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-calendar-range"></i> Period:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($request['period_from']); ?> to <?php echo htmlspecialchars($request['period_to']); ?>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-question-circle"></i> Purpose:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['request_purpose']); ?></div>
            </div>
        </div>

        <?php if (strtolower($request['status']) === 'rejected' && !empty($rejectionReason)): ?>
        <div class="detail-section">
            <h3><i class="bi bi-exclamation-triangle"></i> Rejection Details</h3>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-chat-left-text"></i> Reason for Rejection:</div>
                <div class="detail-value"><?php echo htmlspecialchars($rejectionReason); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="detail-section">
            <h3><i class="bi bi-person-lines-fill"></i> Requester Information</h3>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-person"></i> Full Name:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['fullnames']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-envelope"></i> Email:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['email']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-telephone"></i> Phone:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['phone_number']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-credit-card"></i> KRA PIN:</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['kra_pin']); ?></div>
            </div>
            <?php if ($request['requester_affiliation_name']): ?>
                <div class="detail-row">
                    <div class="detail-label"><i class="bi bi-building"></i> Affiliation:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($request['requester_affiliation_name']); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <?php if (strtolower($request['status']) === 'pending' || strtolower($request['status']) === 'rejected'): ?>
                <a href="edit-request.php?id=<?php echo $requestId; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit Request
                </a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
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
                success: '<i class="bi bi-check-circle-fill"></i>',
                error: '<i class="bi bi-x-circle-fill"></i>',
                info: '<i class="bi bi-info-circle-fill"></i>',
                warning: '<i class="bi bi-exclamation-triangle-fill"></i>'
            };
            notificationIcon.innerHTML = icons[type] || '';

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

ob_end_flush(); // Flush the output buffer