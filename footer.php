<!-- footer.php -->
</div>
<footer class="kra-footer">
    <div class="container">
        <div class="footer-content">
            <p class="footer-text">&copy; <?php echo date("Y"); ?> Kenya Revenue Authority</p>
            <div class="footer-links">
                <a href="assets/images/KRA_WEBSITE_PRIVACY_POLICY_2018.pdf" class="footer-link" target="_blank">Privacy Policy</a>
                <span class="footer-separator">|</span>
                <a href="assets/images/Corporate_Website_Terms_Conditions.pdf" class="footer-link" target="_blank">Terms and Conditions</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Notification function
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationMessage = document.getElementById('notificationMessage');

        notification.className = 'notification';
        notification.classList.add(type);

        const icons = {
            success: '✓',
            error: '✗',
            info: 'ℹ',
            warning: '⚠'
        };
        notificationIcon.textContent = icons[type] || '';
        notificationMessage.textContent = message;
        notification.classList.add('show');

        const autoHide = setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);

        document.getElementById('notificationClose').onclick = function() {
            clearTimeout(autoHide);
            notification.classList.remove('show');
        };
    }

    // Enhanced logout functionality
    async function performLogout() {
        const token = localStorage.getItem('authToken') || '<?php echo $_SESSION['authToken'] ?? ''; ?>';
        
        try {
            // Clear client-side tokens immediately
            localStorage.removeItem('authToken');
            localStorage.removeItem('nda_form');
            localStorage.removeItem('selectedCategory');
            localStorage.removeItem('personalInfo');
            localStorage.removeItem('taxAgentInfo');
            localStorage.removeItem('clientInfo');

            // Call server-side logout
            const response = await fetch('api/logout.php', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('Logout failed');
            }

            // Redirect to login page
            window.location.href = 'login.html';
            
        } catch (error) {
            console.error('Logout error:', error);
            showNotification('Logout failed. Please try again.', 'error');
            
            // Fallback redirect if everything fails
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 3000);
        }
    }

    // Session management
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize modal
        const sessionModal = new bootstrap.Modal(document.getElementById('sessionTimeoutModal'));
        let sessionCountdownInterval;
        
        // Handle logout link
        const logoutLink = document.getElementById('logoutLink');
        if (logoutLink) {
            logoutLink.addEventListener('click', async function(e) {
                e.preventDefault();
                const spinner = document.getElementById('logoutSpinner');
                
                try {
                    // Show loading state
                    logoutLink.style.pointerEvents = 'none';
                    spinner.style.display = 'inline-block';
                    
                    await performLogout();
                } catch (error) {
                    console.error('Logout error:', error);
                    logoutLink.style.pointerEvents = 'auto';
                    spinner.style.display = 'none';
                }
            });
        }

        // Check for session warning
        const sessionWarning = <?php echo isset($_SESSION['session_warning']) ? json_encode($_SESSION['session_warning']) : 'null'; ?>;
        if (sessionWarning && sessionWarning.show) {
            showSessionWarning(sessionWarning.remaining, sessionModal);
        }

        // Start session checker
        startSessionChecker();
    });

    // Session timeout checker
    const SESSION_CHECK_INTERVAL = 60000; // Check every minute
    const WARNING_THRESHOLD = 300; // 5 minutes in seconds
    let sessionCheckInterval;

    function startSessionChecker() {
        // Clear any existing interval
        if (sessionCheckInterval) {
            clearInterval(sessionCheckInterval);
        }

        // Initial check
        checkSessionStatus();

        // Set up periodic checking
        sessionCheckInterval = setInterval(checkSessionStatus, SESSION_CHECK_INTERVAL);
    }

    async function checkSessionStatus() {
        const token = localStorage.getItem('authToken') || '<?php echo $_SESSION['authToken'] ?? ''; ?>';
        
        if (!token) {
            console.error('No token available for session check');
            return;
        }

        try {
            const response = await fetch('api/check_session.php', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                credentials: 'include'
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw data;
            }

            if (data.shouldWarn) {
                showSessionWarning(data.remaining, new bootstrap.Modal(document.getElementById('sessionTimeoutModal')));
            }
        } catch (error) {
            console.error('Session check failed:', error);
            if (error.message && error.message.includes('No session token found')) {
                performAutoLogout();
            }
        }
    }

    function showSessionWarning(remainingSeconds, modal) {
        const countdownElement = document.getElementById('sessionCountdown');
        const logoutNowBtn = document.getElementById('logoutNowBtn');
        const extendSessionBtn = document.getElementById('extendSessionBtn');

        // Clear any existing countdown
        if (window.sessionCountdownInterval) {
            clearInterval(window.sessionCountdownInterval);
        }

        // Update countdown display
        function updateCountdown() {
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            remainingSeconds--;
            
            if (remainingSeconds <= 0) {
                clearInterval(window.sessionCountdownInterval);
                performAutoLogout();
            }
        }

        // Show modal and start countdown
        modal.show();
        updateCountdown();
        window.sessionCountdownInterval = setInterval(updateCountdown, 1000);

        // Setup button handlers
        extendSessionBtn.onclick = async function() {
            try {
                const response = await fetch('api/refresh_session.php');
                const data = await response.json();
                
                if (data.success) {
                    modal.hide();
                    showNotification('Session extended', 'success');
                    // Reset the modal backdrop if it remains
                    document.querySelector('.modal-backdrop').remove();
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            } catch (error) {
                console.error('Session extension failed:', error);
                showNotification('Failed to extend session', 'error');
            }
        };

        logoutNowBtn.onclick = function() {
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out...';
            this.disabled = true;
            performLogout().finally(() => {
                this.innerHTML = 'Logout Now';
                this.disabled = false;
            });
        };
    }

    async function performAutoLogout() {
        try {
            // Clear client-side storage
            localStorage.removeItem('authToken');
            localStorage.removeItem('nda_form');
            localStorage.removeItem('selectedCategory');
            
            // Redirect to login with session expired message
            window.location.href = 'login.html?session=expired';
        } catch (error) {
            console.error('Auto logout error:', error);
            window.location.href = 'login.html';
        }
    }
</script>
</body>
</html>