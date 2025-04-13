<?php
include 'header.php';
require_once 'auth.php';
require_once 'config.php';

try {
    // Authenticate user
    $userId = authenticate();

    // Fetch user data
    $stmt = $conn->prepare("SELECT email, is_active FROM ext_users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception('User not found');
    }

    if (!$user['is_active']) {
        throw new Exception('Account is inactive');
    }

    // Get token from session or headers
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f8f9fa;
                height: 100vh;
            }

            .dashboard-container {
                background: white;
                padding: 2rem;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                position: relative;
                margin: 20px;
                height: calc(100% - 40px);
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
            }

            .user-info {
                background: #f5f5f5;
                padding: 20px;
                border-radius: 5px;
                margin-bottom: 20px;
            }

            .action-buttons {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
            }

            #logout,
            #createRequest {
                color: white;
                border: none;
                padding: 10px 15px;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-size: 16px;
            }

            #logout {
                background: #dc3545;
            }

            #logout:hover {
                background: #c82333;
            }

            #createRequest {
                background: #007bff;
            }

            #createRequest:hover {
                background: #0069d9;
            }

            .loading {
                display: none;
                margin-left: 10px;
                border: 3px solid rgba(255, 255, 255, .3);
                border-radius: 50%;
                border-top: 3px solid white;
                width: 16px;
                height: 16px;
                animation: spin 1s linear infinite;
                vertical-align: middle;
            }

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

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            .requests-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                flex-grow: 1;
                overflow: hidden;
            }

            .requests-table th,
            .requests-table td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }

            .requests-table th {
                background-color: #f8f9fa;
                font-weight: 600;
            }

            .requests-table tr:hover {
                background-color: #f5f5f5;
            }

            .no-requests {
                text-align: center;
                padding: 20px;
                color: #666;
                font-style: italic;
            }

            .table-container {
                flex-grow: 1;
                overflow-y: auto;
                margin-bottom: 20px;
                border: 1px solid #eee;
                border-radius: 5px;
            }

            .status-pending {
                color: #ffc107;
                font-weight: 600;
            }

            .status-approved {
                color: #28a745;
                font-weight: 600;
            }

            .status-rejected {
                color: #dc3545;
                font-weight: 600;
            }

            .status-processing {
                color: #17a2b8;
                font-weight: 600;
            }

            .table-loading {
                display: none;
                text-align: center;
                padding: 20px;
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

        <div class="dashboard-container">
            <div class="user-info">
                <h1>Welcome, <?php echo htmlspecialchars($user['email']); ?></h1>
                <p>You are successfully logged in to your account.</p>
            </div>

            <div class="action-buttons">
                <button id="createRequest">
                    Create New Request
                    <span class="loading" id="createRequestSpinner"></span>
                </button>
                <button id="logout">
                    Logout
                    <span class="loading" id="logoutSpinner"></span>
                </button>
            </div>
            <div id="tableLoading" class="table-loading">
                Loading requests... <span class="loading"></span>
            </div>
            <div class="table-container">
                <table class="requests-table" id="requestsTable">
                    <thead>
                        <tr>
                            <th>Tracking #</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                        <!-- Requests will be loaded here -->
                    </tbody>
                </table>
                <div id="noRequestsMessage" class="no-requests" style="display: none;">
                    No requests found. Click "Create New Request" to get started.
                </div>
            </div>
        </div>

        <script>
            // Store token in localStorage if it came from session
            const token = '<?php echo $token; ?>';
            if (token && !localStorage.getItem('authToken')) {
                localStorage.setItem('authToken', token);
            }

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

            // Check for session messages
            function checkForSessionMessages() {
                fetch('api/check-messages.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            showNotification(data.message, data.type || 'info');
                        }
                    })
                    .catch(error => console.error('Error checking messages:', error));
            }

            // Create New Request button functionality
            document.getElementById('createRequest').addEventListener('click', function() {
                window.location.href = 'request.php';
            });

            // Enhanced logout functionality
            document.getElementById('logout').addEventListener('click', async function() {
                const logoutBtn = this;
                const spinner = document.getElementById('logoutSpinner');

                try {
                    // Show loading state
                    logoutBtn.disabled = true;
                    spinner.style.display = 'inline-block';

                    // Clear client-side token immediately
                    localStorage.removeItem('authToken');

                    // Call server-side logout
                    const response = await fetch('api/logout.php', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Logout failed');
                    }

                    // Show notification and redirect
                    showNotification('Logout successful! Redirecting to login page...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 1500);
                } catch (error) {
                    console.error('Logout error:', error);
                    showNotification(error.message, 'error');
                } finally {
                    logoutBtn.disabled = false;
                    spinner.style.display = 'none';
                }
            });

            // Token validation on page load
            window.addEventListener('load', async () => {
                // Check for any session messages first
                checkForSessionMessages();

                if (!localStorage.getItem('authToken')) {
                    window.location.href = 'login.html';
                    return;
                }

                try {
                    const response = await fetch('api/validate-token.php', {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                        }
                    });

                    if (!response.ok) {
                        localStorage.removeItem('authToken');
                        window.location.href = 'login.html';
                        return;
                    }

                    // Fetch user requests after successful token validation
                    fetchUserRequests();
                    
                    // Refresh requests every 30 seconds
                    setInterval(fetchUserRequests, 30000);

                } catch (error) {
                    console.error('Token validation error:', error);
                    window.location.href = 'login.html';
                }
            });

            async function fetchUserRequests() {
                const tableLoading = document.getElementById('tableLoading');
                tableLoading.style.display = 'block';

                try {
                    const response = await fetch('api/get-user-requests.php', {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to fetch requests');
                    }

                    const data = await response.json();

                    const tableBody = document.getElementById('requestsTableBody');
                    const noRequestsMessage = document.getElementById('noRequestsMessage');

                    tableBody.innerHTML = '';

                    if (data.data && data.data.length > 0) {
                        noRequestsMessage.style.display = 'none';

                        data.data.forEach(request => {
                            const row = document.createElement('tr');

                            // Determine status class
                            let statusClass = '';
                            if (request.status.toLowerCase().includes('pending')) {
                                statusClass = 'status-pending';
                            } else if (request.status.toLowerCase().includes('approved')) {
                                statusClass = 'status-approved';
                            } else if (request.status.toLowerCase().includes('rejected')) {
                                statusClass = 'status-rejected';
                            } else if (request.status.toLowerCase().includes('processing')) {
                                statusClass = 'status-processing';
                            }

                            // Shorten description if too long
                            const shortDescription = request.data_description.length > 50 ?
                                request.data_description.substring(0, 47) + '...' :
                                request.data_description;

                            row.innerHTML = `
                                <td>${request.tracking_number || 'N/A'}</td>
                                <td>${request.date}</td>
                                <td>${request.category || 'N/A'}</td>
                                <td title="${request.data_description}">${shortDescription}</td>
                                <td class="${statusClass}">${request.status || 'N/A'}</td>
                                <td>
                                    <button class="view-request" data-id="${request.id}">View</button>
                                </td>
                            `;

                            tableBody.appendChild(row);
                        });

                        // Add event listeners to view buttons
                        document.querySelectorAll('.view-request').forEach(button => {
                            button.addEventListener('click', function() {
                                const requestId = this.getAttribute('data-id');
                                viewRequestDetails(requestId);
                            });
                        });
                    } else {
                        noRequestsMessage.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error fetching requests:', error);
                    showNotification('Failed to load requests. Please try again.', 'error');
                } finally {
                    tableLoading.style.display = 'none';
                }
            }

            function viewRequestDetails(requestId) {
                window.location.href = `request-details.php?id=${requestId}`;
            }
        </script>
    </body>
    </html>
<?php
} catch (Exception $e) {
    // Clear any existing tokens on error
    if (isset($_SESSION['authToken'])) {
        unset($_SESSION['authToken']);
    }

    // Store error message in session
    $_SESSION['notification'] = [
        'message' => $e->getMessage(),
        'type' => 'error'
    ];

    // For AJAX requests, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required',
            'redirect' => 'login.html'
        ]);
    } else {
        // For regular page loads, redirect to login
        header('Location: login.html');
    }
    exit;
}

include 'footer.php';