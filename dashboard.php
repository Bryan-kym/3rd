<?php
ob_start();
require_once 'auth.php';
require_once 'config.php';
include 'header.php';

try {
    // Authenticate user
    $userId = authenticate();

    // Fetch user data
    $stmt = $conn->prepare("SELECT surname, email, is_active FROM ext_users WHERE id = ?");
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
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            :root {
                --primary-color: #d9232e;
                /* KRA Red */
                --secondary-color: #151515;
                /* Dark Black */
                --light-color: #ffffff;
                /* White */
                --grey-color: #6c757d;
                /* Grey */
                --dark-grey: #343a40;
                /* Dark Grey */
                --success-color: #28a745;
                --warning-color: #ffc107;
                --danger-color: #dc3545;
                --border-radius: 0.375rem;
                --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                --transition: all 0.3s ease;
            }

            body {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                margin: 0;
                background-color: #f8f8f8;
                color: var(--secondary-color);
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .main-content-wrapper {
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .dashboard-container {
                flex: 1;
                padding: 2rem;
                background: white;
            }

            .welcome-card {
                background: linear-gradient(135deg, var(--primary-color), #a81a22, var(--secondary-color));
                /* background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); */
                color: var(--light-color);
                padding: 2rem;
                border-radius: var(--border-radius);
                margin-bottom: 2rem;
                position: relative;
                overflow: hidden;
            }

            .welcome-card::after {
                content: "";
                position: absolute;
                top: -50%;
                right: -50%;
                width: 100%;
                height: 200%;
                background: rgba(255, 255, 255, 0.1);
                transform: rotate(30deg);
            }

            .welcome-card h1 {
                font-weight: 600;
                margin-bottom: 0.5rem;
                font-size: 2rem;
            }

            .welcome-card p {
                opacity: 0.9;
                margin-bottom: 0;
                font-size: 1.1rem;
            }

            .stat-card h3 {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .stat-card h3::before {
                font-family: "Bootstrap Icons";
                font-size: 1rem;
            }

            .stat-card:nth-child(1) h3::before {
                content: "\F479";
            }

            /* Total - clipboard */
            .stat-card:nth-child(2) h3::before {
                content: "\F26C";
            }

            /* Approved - check */
            .stat-card:nth-child(3) h3::before {
                content: "\F28A";
            }

            /* Pending - clock */
            .stat-card:nth-child(4) h3::before {
                content: "\F3F1";
            }

            /* Processing - gear */

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .stat-card {
                background: var(--light-color);
                padding: 1.5rem;
                border-radius: var(--border-radius);
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                transition: var(--transition);
                border-left: 4px solid var(--primary-color);
            }

            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: var(--box-shadow);
            }

            .stat-card h3 {
                font-size: 0.875rem;
                text-transform: uppercase;
                color: var(--grey-color);
                margin-bottom: 0.5rem;
            }

            .stat-card .value {
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
                color: var(--secondary-color);
            }

            .stat-card .change {
                font-size: 0.875rem;
                display: flex;
                align-items: center;
                color: var(--grey-color);
            }

            .stat-card .change.positive {
                color: var(--success-color);
            }

            .stat-card .change.negative {
                color: var(--danger-color);
            }

            .action-buttons {
                display: flex;
                gap: 1rem;
                margin-bottom: 2rem;
                flex-wrap: wrap;
            }

            .btn-primary {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
                padding: 0.75rem 1.5rem;
                font-weight: 500;
                transition: var(--transition);
                color: white;
                border-radius: var(--border-radius);
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                cursor: pointer;
                border: none;
            }

            .btn-primary:hover {
                background-color: #b51d27;
                transform: translateY(-2px);
            }

            .btn-outline {
                background-color: transparent;
                border: 1px solid var(--primary-color);
                color: var(--primary-color);
                padding: 0.75rem 1.5rem;
                font-weight: 500;
                transition: var(--transition);
                border-radius: var(--border-radius);
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                cursor: pointer;
            }

            .btn-outline:hover {
                background-color: rgba(217, 35, 46, 0.15);
                transform: translateY(-2px) scale(1.02);
                box-shadow: 0 2px 8px rgba(217, 35, 46, 0.1);
            }

            .table-container {
                background: white;
                border-radius: var(--border-radius);
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                overflow: hidden;
                width: 100%;
                max-height: 60vh;
                overflow-y: auto;
                margin-bottom: 1rem;
            }

            .requests-table {
                width: 100%;
                border-collapse: collapse;
            }

            .requests-table thead {
                background-color: #f8f9fa;
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .requests-table th {
                padding: 1rem 1.5rem;
                text-align: left;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.75rem;
                letter-spacing: 0.5px;
                color: var(--grey-color);
                background-color: var(--light-color);
            }

            .requests-table td {
                padding: 1rem 1.5rem;
                border-top: 1px solid #eee;
                vertical-align: middle;
            }

            .requests-table tr:hover {
                background-color: #f1f1f1;
                transition: var(--transition);
            }

            .status-badge {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
                letter-spacing: 0.5px;
            }

            .status-pending {
                background-color: rgba(255, 193, 7, 0.1);
                color: var(--warning-color);
            }

            .status-approved {
                background-color: rgba(40, 167, 69, 0.1);
                color: var(--success-color);
            }

            .status-rejected {
                background-color: rgba(220, 53, 69, 0.1);
                color: var(--danger-color);
            }

            .status-processing {
                background-color: rgba(217, 35, 46, 0.1);
                color: var(--primary-color);
            }

            .action-btn {
                padding: 0.5rem 1rem;
                border-radius: var(--border-radius);
                font-size: 0.875rem;
                font-weight: 500;
                transition: var(--transition);
                border: none;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .view-btn {
                background-color: rgba(217, 35, 46, 0.1);
                color: var(--primary-color);
            }

            .view-btn:hover {
                background-color: rgba(217, 35, 46, 0.2);
            }

            .no-requests {
                text-align: center;
                padding: 3rem;
                color: var(--grey-color);
            }

            .no-requests i {
                font-size: 3rem;
                margin-bottom: 1rem;
                color: #dee2e6;
            }

            .loading {
                display: inline-block;
                width: 1.5rem;
                height: 1.5rem;
                border: 3px solid rgba(0, 0, 0, 0.1);
                border-radius: 50%;
                border-top-color: var(--primary-color);
                animation: spin 1s linear infinite;
                vertical-align: middle;
                margin-left: 0.5rem;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            /* Toast notifications */
            .toast {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
                display: flex;
                align-items: center;
                gap: 1rem;
                z-index: 1000;
                transform: translateX(200%);
                transition: var(--transition);
                opacity: 0;
            }

            .toast.show {
                transform: translateX(0);
                opacity: 1;
            }

            .toast.success {
                background-color: var(--success-color);
                color: white;
            }

            .toast.error {
                background-color: var(--danger-color);
                color: white;
            }

            .toast-icon {
                font-size: 1.25rem;
            }

            .toast-close {
                background: transparent;
                border: none;
                color: inherit;
                cursor: pointer;
                padding: 0;
                margin-left: 1rem;
            }

            /* Footer Styles */
            footer.kra-footer {
                background-color: var(--secondary-color);
                color: var(--light-color);
                padding: 1.5rem 0;
                margin-top: auto;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                width: 100%;
            }

            .footer-content {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
                text-align: center;
            }

            .footer-text {
                margin: 0;
                font-size: 0.9rem;
                color: rgba(255, 255, 255, 0.8);
            }

            .footer-links {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .footer-link {
                color: rgba(255, 255, 255, 0.8);
                text-decoration: none;
                font-size: 0.85rem;
                transition: color 0.2s ease;
            }

            .footer-link:hover {
                color: var(--light-color);
                text-decoration: underline;
            }

            .footer-separator {
                color: rgba(255, 255, 255, 0.5);
                font-size: 0.85rem;
            }

            /* Pagination Controls */
            .pagination-controls {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 1rem;
                margin: 1rem 0;
                padding: 1rem;
                background-color: var(--light-color);
                border-radius: var(--border-radius);
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }

            .pagination-controls button[disabled] {
                opacity: 0.5;
                cursor: not-allowed;
            }

            /* Responsive adjustments */
            @media (max-width: 992px) {
                .dashboard-container {
                    padding: 1.5rem;
                }

                .welcome-card {
                    padding: 1.5rem;
                }
            }

            @media (max-width: 768px) {
                .dashboard-container {
                    padding: 1rem;
                }

                .welcome-card h1 {
                    font-size: 1.5rem;
                }

                .stats-grid {
                    grid-template-columns: 1fr;
                }

                .requests-table th,
                .requests-table td {
                    padding: 0.75rem;
                }

                .footer-links {
                    flex-direction: column;
                    gap: 0.5rem;
                }

                .footer-separator {
                    display: none;
                }
            }

            @media (max-width: 576px) {
                .action-buttons {
                    flex-direction: column;
                    gap: 0.75rem;
                }

                .btn-primary,
                .btn-outline {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
    </head>

    <body>
        <div class="main-content-wrapper">
            <div class="dashboard-container">
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h1>Welcome back, <?php echo htmlspecialchars($user['surname']); ?></h1>
                    <p>Here's what's happening with your requests today</p>
                </div>

                <!-- Stats Overview -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Requests</h3>
                        <div class="value" id="totalRequests">--</div>
                        <div class="change positive">
                            <!-- <i class="bi bi-arrow-up"></i> <span id="requestsChange">Loading...</span> -->
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Approved</h3>
                        <div class="value" id="approvedRequests">--</div>
                        <div class="change positive">
                            <!-- <i class="bi bi-arrow-up"></i> <span id="approvedChange">Loading...</span> -->
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending</h3>
                        <div class="value" id="pendingRequests">--</div>
                        <div class="change negative">
                            <!-- <i class="bi bi-arrow-down"></i> <span id="pendingChange">Loading...</span> -->
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Processing</h3>
                        <div class="value" id="processingRequests">--</div>
                        <div class="change positive">
                            <!-- <i class="bi bi-arrow-up"></i> <span id="processingChange">Loading...</span> -->
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button id="createRequest" class="btn-primary">
                        <i class="bi bi-plus-circle"></i> Create New Request
                    </button>
                    <button id="refreshRequests" class="btn-outline">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>

                <!-- Requests Table -->
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
                        <i class="bi bi-inbox"></i>
                        <h4>No requests found</h4>
                        <p>Get started by creating your first request</p>
                        <button id="createFirstRequest" class="btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Request
                        </button>
                    </div>
                    <div id="tableLoading" style="text-align: center; padding: 2rem; display: none;">
                        <div class="loading" style="margin: 0 auto;"></div>
                        <p>Loading your requests...</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Store token in localStorage if it came from session
            const token = '<?php echo $token; ?>';
            if (token && !localStorage.getItem('authToken')) {
                localStorage.setItem('authToken', token);
            }

            // DOM Elements
            const createRequestBtn = document.getElementById('createRequest');
            const createFirstRequestBtn = document.getElementById('createFirstRequest');
            const refreshRequestsBtn = document.getElementById('refreshRequests');
            const requestsTableBody = document.getElementById('requestsTableBody');
            const noRequestsMessage = document.getElementById('noRequestsMessage');
            const tableLoading = document.getElementById('tableLoading');

            // Pagination variables
            let currentPage = 1;
            const rowsPerPage = 10;

            // Initialize dashboard
            document.addEventListener('DOMContentLoaded', async () => {
                // Check authentication
                if (!localStorage.getItem('authToken')) {
                    window.location.href = 'login.html';
                    return;
                }

                // Set up event listeners
                createRequestBtn.addEventListener('click', () => window.location.href = 'request.php');
                createFirstRequestBtn.addEventListener('click', () => window.location.href = 'request.php');
                refreshRequestsBtn.addEventListener('click', () => {
                    currentPage = 1;
                    fetchDashboardData();
                });

                // Load initial data
                await fetchDashboardData();

                // Refresh data every 30 seconds
                setInterval(fetchDashboardData, 30000);
            });

            // Fetch all dashboard data
            async function fetchDashboardData() {
                try {
                    tableLoading.style.display = 'block';
                    requestsTableBody.innerHTML = '';

                    // Validate token first
                    const tokenValid = await validateToken();
                    if (!tokenValid) {
                        window.location.href = 'login.html';
                        return;
                    }

                    // Fetch stats and requests in parallel
                    const [stats, requests] = await Promise.all([
                        fetchUserStats(),
                        fetchUserRequests()
                    ]);

                    updateStats(stats);
                    updateRequestsTable(requests);

                } catch (error) {
                    console.error('Dashboard error:', error);
                    showErrorToast('Failed to load dashboard data');
                } finally {
                    tableLoading.style.display = 'none';
                }
            }

            // Validate token with server
            async function validateToken() {
                try {
                    const response = await fetch('api/validate-token.php', {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                        }
                    });
                    return response.ok;
                } catch (error) {
                    return false;
                }
            }

            // Fetch user statistics
            async function fetchUserStats() {
                const response = await fetch('api/get-user-stats.php', {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch stats');
                }

                return await response.json();
            }

            // Fetch user requests
            async function fetchUserRequests() {
                const response = await fetch('api/get-user-requests.php', {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch requests');
                }

                return await response.json();
            }

            // Update stats cards
            function updateStats(stats) {
                document.getElementById('totalRequests').textContent = stats.total_requests || 0;
                document.getElementById('approvedRequests').textContent = stats.approved_requests || 0;
                document.getElementById('pendingRequests').textContent = stats.pending_requests || 0;
                document.getElementById('processingRequests').textContent = stats.processing_requests || 0;
            }

            // Update requests table with pagination
            function updateRequestsTable(response) {
                requestsTableBody.innerHTML = '';

                // Remove existing pagination controls
                document.querySelector('.pagination-controls')?.remove();

                // Check if response has data array
                if (response.success && response.data && response.data.length > 0) {
                    noRequestsMessage.style.display = 'none';

                    // Calculate pagination
                    const start = (currentPage - 1) * rowsPerPage;
                    const end = start + rowsPerPage;
                    const paginatedData = response.data.slice(start, end);

                    // Render paginated data
                    paginatedData.forEach(request => {
                        const row = document.createElement('tr');

                        // Determine status class and icon
                        let statusClass = '';
                        let statusIcon = '';
                        const status = request.request_status ? request.request_status.toLowerCase() : '';

                        if (status.includes('pending')) {
                            statusClass = 'status-pending';
                            statusIcon = '<i class="bi bi-clock"></i> ';
                        } else if (status.includes('approved') || status.includes('resolved') || status.includes('completed')) {
                            statusClass = 'status-approved';
                            statusIcon = '<i class="bi bi-check-circle"></i> ';
                        } else if (status.includes('rejected') || status.includes('denied')) {
                            statusClass = 'status-rejected';
                            statusIcon = '<i class="bi bi-x-circle"></i> ';
                        } else if (status.includes('processing') || status.includes('assigned') || status.includes('in progress')) {
                            statusClass = 'status-processing';
                            statusIcon = '<i class="bi bi-gear"></i> ';
                        }

                        // Use the formatted date from the response
                        const formattedDate = request.request_date ? new Date(request.request_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        }) : 'N/A';

                        // Handle description
                        const description = request.data_description || 'No description';
                        const shortDescription = description.length > 50 ?
                            description.substring(0, 47) + '...' :
                            description;

                        row.innerHTML = `
                        <td>${request.tracking_number || 'N/A'}</td>
                        <td>${formattedDate}</td>
                        <td>${request.category || 'N/A'}</td>
                        <td title="${description}">${shortDescription}</td>
                        <td><span class="status-badge ${statusClass}">${statusIcon}${request.request_status || 'N/A'}</span></td>
                        <td>
                            <button class="action-btn view-btn" data-id="${request.id}">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    `;

                        requestsTableBody.appendChild(row);
                    });

                    // Add pagination controls if needed
                    if (response.data.length > rowsPerPage) {
                        addPaginationControls(response.data.length);
                    }

                    // Add event listeners to view buttons
                    document.querySelectorAll('.view-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const requestId = this.getAttribute('data-id');
                            window.location.href = `request-details.php?id=${requestId}`;
                        });
                    });

                } else {
                    noRequestsMessage.style.display = 'block';
                }
            }

            // Add pagination controls
            function addPaginationControls(totalRows) {
                const totalPages = Math.ceil(totalRows / rowsPerPage);

                const paginationDiv = document.createElement('div');
                paginationDiv.className = 'pagination-controls';
                paginationDiv.innerHTML = `
                <button id="prevPage" class="btn-outline" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="bi bi-chevron-left"></i> Previous
                </button>
                <span>Page ${currentPage} of ${totalPages}</span>
                <button id="nextPage" class="btn-outline" ${currentPage === totalPages ? 'disabled' : ''}>
                    Next <i class="bi bi-chevron-right"></i>
                </button>
            `;

                // Insert after table container
                document.querySelector('.table-container').after(paginationDiv);

                // Add event listeners
                document.getElementById('prevPage')?.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        fetchDashboardData();
                    }
                });

                document.getElementById('nextPage')?.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        fetchDashboardData();
                    }
                });
            }

            // Toast notification functions
            function showSuccessToast(message) {
                showToast(message, 'success');
            }

            function showErrorToast(message) {
                showToast(message, 'error');
            }

            function showToast(message, type) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.innerHTML = `
                <div class="toast-icon">
                    ${type === 'success' ? '<i class="bi bi-check-circle"></i>' : '<i class="bi bi-exclamation-circle"></i>'}
                </div>
                <div class="toast-message">${message}</div>
                <button class="toast-close"><i class="bi bi-x"></i></button>
            `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);

                // Auto-dismiss
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 5000);

                // Manual dismiss
                toast.querySelector('.toast-close').addEventListener('click', () => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                });
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
ob_end_flush();
