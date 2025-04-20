<?php
ob_start();
require_once 'auth.php';
include 'header.php';

ob_start();
require_once 'auth.php';
include 'header.php';

try {
    $userId = authenticate();
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
} catch (Exception $e) {
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 pb-1">
                    <h3 class="card-title text-center text-primary mb-1">Personal Information</h3>
                    <p class="text-muted text-center mb-0">Please complete your personal details</p>
                </div>

                <!-- Wrap the rest of the form fields in a div -->
                <div id="otherFields" style="display: none;">
                    <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" class="form-control" id="surname" name="surname" required disabled>
                    </div>

                    <div class="form-group">
                        <label for="othernames">Other Names</label>
                        <input type="text" class="form-control" id="othernames" name="othernames" required disabled>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required disabled>
                        <small id="emailHelp" class="form-text text-muted">Please enter a valid email address.</small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" pattern="[0-9]{10}" required disabled>
                        <small id="phoneHelp" class="form-text text-muted">Please enter a 10-digit phone number.</small>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

<!-- OTP Modal -->
<div class="modal fade" id="otpModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify Your Email</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>An OTP has been sent to your email. Please enter it below:</p>
                <input type="text" class="form-control" id="otpInput" placeholder="Enter OTP">
                <p id="otpError" class="text-danger"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="resendOtpBtn">Resend OTP</button>
                <button type="button" class="btn btn-primary" id="verifyOtpBtn">Verify OTP</button>
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

    document.addEventListener('DOMContentLoaded', async function() {
        // Check if coming from proper flow
        if (!localStorage.getItem('authToken') ||
            !localStorage.getItem('nda_form') ||
            !localStorage.getItem('selectedCategory')) {
            window.location.href = 'dashboard.php';
            return;
        }

        try {
            // Validate token with server
            const tokenResponse = await fetch('api/validate-token.php', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                }
            });

            if (!tokenResponse.ok) {
                localStorage.removeItem('authToken');
                localStorage.removeItem('nda_form');
                localStorage.removeItem('selectedCategory');
                window.location.href = 'login.html';
                return;
            }

            // Fetch and populate user data
            await fetchUserData();

            // Load any previously saved form data
            loadFormData();

        } catch (error) {
            console.error('Initialization error:', error);
            window.location.href = 'login.html';
        }
    });

    async function fetchUserData() {
        try {
            const response = await fetch('api/get_user_data.php', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch user data');
            }

            const result = await response.json();

            if (result.success && result.data) {
                const userData = result.data;
                document.getElementById('kra_pin').value = userData.kra_pin || '';
                document.getElementById('surname').value = userData.surname || '';
                document.getElementById('othernames').value = userData.othernames || '';
                document.getElementById('email').value = userData.email || '';
                document.getElementById('phone').value = userData.phone || '';

                // Make email readonly since it's from registration
                document.getElementById('email').readOnly = true;
            }
        } catch (error) {
            console.error('Error fetching user data:', error);
            showAlert('warning', 'Could not load your registration details. Please fill the form manually.');
        }
    }

    function loadFormData() {
        const savedData = localStorage.getItem('personalInfo');
        if (savedData) {
            try {
                const formData = JSON.parse(savedData);
                // Only load data if fields are empty
                if (!document.getElementById('surname').value) document.getElementById('surname').value = formData.surname || '';
                if (!document.getElementById('othernames').value) document.getElementById('othernames').value = formData.othernames || '';
                if (!document.getElementById('kra_pin').value) document.getElementById('kra_pin').value = formData.kra_pin || '';
                if (!document.getElementById('phone').value) document.getElementById('phone').value = formData.phone || '';
            } catch (e) {
                console.error('Error loading saved data:', e);
            }
        }
    }

    function saveFormData() {
        const formData = {
            surname: document.getElementById('surname').value,
            othernames: document.getElementById('othernames').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            kra_pin: document.getElementById('kra_pin').value
        };
        localStorage.setItem('personalInfo', JSON.stringify(formData));
    }

    function validateEmail(email) {
        const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return regex.test(email);
    }

    function validatePhone(phone) {
        // Basic international phone validation (with optional + and country code)
        const regex = /^\+?[0-9\s\-]{10,15}$/;
        return regex.test(phone);
    }

    function showAlert(type, message) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.card-body').prepend(alert);
    }

    // Initialize next button click handler after DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('nextBtn').addEventListener('click', function() {
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;

            if (!validateEmail(email)) {
                showAlert('danger', 'Please enter a valid email address.');
                return;
            }

            if (!validatePhone(phone)) {
                showAlert('danger', 'Please enter a valid phone number with country code.');
                return;
            }

            saveFormData();

            const category = localStorage.getItem('selectedCategory');
            if (category === 'student' || category === 'researcher') {
                window.location.href = 'institution_details.php';
            } else if (category === 'privatecompany' || category === 'publiccompany') {
                window.location.href = 'org.php';
            } else {
                window.location.href = 'data_request.php';
            }
        });

        document.getElementById('backBtn').addEventListener('click', function() {
            saveFormData();
            window.location.href = 'options.php';
        });
    });
</script>

<?php
ob_flush();
include 'footer.php';