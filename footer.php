<!-- footer.php -->
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
        document.getElementById('logoutLink').addEventListener('click', async function(e) {
            e.preventDefault();
            const logoutLink = this;
            const spinner = document.getElementById('logoutSpinner');
            const token = localStorage.getItem('authToken') || '<?php echo $_SESSION['authToken'] ?? ''; ?>';

            try {
                // Show loading state
                logoutLink.style.pointerEvents = 'none';
                spinner.style.display = 'inline-block';

                // Clear client-side tokens
                localStorage.removeItem('authToken');
                localStorage.removeItem('nda_form');
                localStorage.removeItem('selectedCategory');
                localStorage.removeItem('personalInfo');

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
                    const result = await response.json();
                    throw new Error(result.message || 'Logout failed');
                }

                // Clear PHP session
                await fetch('api/clear_session.php', {
                    method: 'POST',
                    credentials: 'include'
                });

                // Show notification and redirect
                showNotification('Logout successful! Redirecting to login page...', 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);

            } catch (error) {
                console.error('Logout error:', error);
                showNotification(error.message || 'Logout failed. Please try again.', 'error');
                logoutLink.style.pointerEvents = 'auto';
                spinner.style.display = 'none';
            }
        });
    </script>
</body>
</html>