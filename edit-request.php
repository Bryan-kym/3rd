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
    <title>Edit Request</title>
    <style>
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px;
            max-width: 800px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea.form-control {
            min-height: 100px;
        }
        
        .form-actions {
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
            font-size: 16px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #0069d9;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }

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

    <div class="container">
        <h1>Edit Request (<?php echo htmlspecialchars($request['tracking_id']); ?>)</h1>
        
        <form id="editRequestForm">
            <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
            
            <div class="form-group">
                <label>Data Description</label>
                <textarea name="description" class="form-control" required><?php 
                    echo htmlspecialchars($request['description']); 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Specific Fields Needed</label>
                <textarea name="specific_fields" class="form-control"><?php 
                    echo htmlspecialchars($request['specific_fields']); 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Request Purpose</label>
                <textarea name="request_purpose" class="form-control" required><?php 
                    echo htmlspecialchars($request['request_purpose']); 
                ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="request-details.php?id=<?php echo $requestId; ?>" class="btn btn-secondary">Cancel</a>
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

        document.getElementById('editRequestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Saving...';
                
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
                
                const submitBtn = document.querySelector('#editRequestForm button[type="submit"]');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Save Changes';
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