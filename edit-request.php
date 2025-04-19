<?php
include 'header.php';
require_once 'auth.php';
require_once 'config.php';

try {
    $userId = authenticate();
    $requestId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$requestId) {
        throw new Exception('Invalid request ID');
    }

    // Fetch request data for editing
    $stmt = $conn->prepare("
        SELECT r.*, req.* 
        FROM requests r
        JOIN requestors req ON r.requested_by = req.id
        WHERE r.id = ? AND req.email = (SELECT email FROM ext_users WHERE id = ?)
        AND r.request_status = 'pending'
    ");
    $stmt->bind_param("ii", $requestId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if (!$request) {
        throw new Exception('Request not found or cannot be edited');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Request - KRA 3rd Party Data Request</title>
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

        .edit-container {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin: 2rem auto;
            max-width: 900px;
            position: relative;
            overflow: hidden;
        }

        .edit-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-red), var(--dark-black));
        }

        .edit-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--medium-grey);
        }

        .edit-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark-black);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .edit-header small {
            font-size: 1rem;
            color: var(--dark-grey);
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark-black);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-group label i {
            color: var(--primary-red);
            width: 20px;
            text-align: center;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--medium-grey);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.25rem rgba(217, 35, 46, 0.1);
            outline: none;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
            line-height: 1.6;
        }

        .form-actions {
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
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-red);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--dark-red);
            transform: translateY(-2px);
        }

        .btn-primary:disabled {
            background-color: var(--dark-grey);
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
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
            .edit-container {
                padding: 2rem;
                margin: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .edit-container {
                padding: 1.25rem;
            }

            .edit-header h1 {
                font-size: 1.5rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
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

    <div class="edit-container">
        <div class="edit-header">
            <h1>
                <i class="bi bi-pencil-square"></i>
                Edit Request 
                <small>(<?php echo htmlspecialchars($request['tracking_id']); ?>)</small>
            </h1>
        </div>
        
        <form id="editRequestForm">
            <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
            
            <div class="form-group">
                <label><i class="bi bi-text-paragraph"></i> Data Description</label>
                <textarea name="description" class="form-control" required><?php 
                    echo htmlspecialchars($request['description']); 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label><i class="bi bi-list-check"></i> Specific Fields Needed</label>
                <textarea name="specific_fields" class="form-control"><?php 
                    echo htmlspecialchars($request['specific_fields']); 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label><i class="bi bi-question-circle"></i> Request Purpose</label>
                <textarea name="request_purpose" class="form-control" required><?php 
                    echo htmlspecialchars($request['request_purpose']); 
                ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Changes
                </button>
                <a href="request-details.php?id=<?php echo $requestId; ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
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

        document.getElementById('editRequestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Saving...';
            
            try {
                const formData = new FormData(this);
                const response = await fetch('api/update-request.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Update failed');
                }

                showNotification('Request updated successfully!', 'success');
                
                setTimeout(() => {
                    window.location.href = `request-details.php?id=<?php echo $requestId; ?>`;
                }, 1500);
                
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error updating request: ' + error.message, 'error');
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-save"></i> Save Changes';
            }
        });

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