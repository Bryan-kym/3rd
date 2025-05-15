<?php
include 'header.php';
require_once 'auth.php';

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
                    <h3 class="card-title text-center text-primary mb-1">Tax Agent Registration</h3>
                    <p class="text-muted text-center mb-0">Complete your profile information to proceed</p>
                </div>
                <div class="card-body px-5 pt-4 pb-5">
                    <form id="step3Form" class="needs-validation" novalidate>
                        <div class="form-section mb-4">
                            <h4 class="mb-4">Account Type</h4>
                            <div class="form-group">
                                <label for="userType">I am registering as: <span class="text-danger">*</span></label>
                                <select class="form-control" id="userType" name="userType" style="padding-top: 0.625rem; height:fit-content" required>
                                    <option value="">Select account type</option>
                                    <option value="individual">Individual Tax Agent</option>
                                    <option value="organization">Organization Representative</option>
                                </select>
                            </div>
                        </div>

                        <div id="individualFields" class="form-section mb-4" style="display: none;">
                            <h4 class="mb-4">Personal Information</h4>

                            <div class="form-group">
                                <label for="kra_pin">KRA PIN <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-stretch">
                                    <div class="form-floating flex-grow-1 me-2">
                                        <input type="text" class="form-control" id="kra_pin" name="kra_pin" placeholder="A123456789X" required>
                                        <label for="kra_pin">Enter your KRA PIN</label>
                                    </div>
                                    <button type="button" class="btn btn-primary px-4" id="verifyPinBtn" style="height: calc(3.5rem + 2px);">
                                        Verify
                                        <span class="btn-spinner" id="verifyPinSpinner"></span>
                                    </button>
                                </div>
                                <div id="kraPinFeedback" class="form-feedback mt-1">Please enter and verify your KRA PIN</div>
                                <small class="form-text text-muted">Format: Letter followed by 9 digits and a letter (e.g. A123456789X)</small>
                            </div>

                            <div id="otherFields" style="display: none;">
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="surname" name="surname" placeholder=" " required disabled>
                                            <label for="surname">Surname <span class="text-danger">*</span></label>
                                            <div class="invalid-feedback">Please enter your surname</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="othernames" name="othernames" placeholder=" " required disabled>
                                            <label for="othernames">Other Names <span class="text-danger">*</span></label>
                                            <div class="invalid-feedback">Please enter your other names</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" placeholder=" " required disabled>
                                            <label for="email">Email Address <span class="text-danger">*</span></label>
                                            <div class="invalid-feedback">Please enter a valid email address</div>
                                            <div id="emailFeedback" class="form-feedback">Please enter a valid email address</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder=" " required disabled>
                                            <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                            <div class="invalid-feedback">Please enter a valid phone number</div>
                                            <div id="phoneFeedback" class="form-feedback">Please enter a 10-digit phone number</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="organizationFields" class="form-section mb-4" style="display: none;">
                            <h4 class="mb-4">Organization Information</h4>

                            <div class="form-group">
                                <label for="orgKraPin">Organization KRA PIN <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-stretch">
                                    <div class="form-floating flex-grow-1 me-2">
                                        <input type="text" class="form-control" id="orgKraPin" name="orgKraPin" placeholder="P123456789X" required>
                                        <label for="orgKraPin">Enter organization KRA PIN</label>
                                    </div>
                                    <button type="button" class="btn btn-primary px-4" id="verifyKraPinBtn" style="height: calc(3.5rem + 2px);">
                                        Verify
                                        <span class="btn-spinner" id="verifyOrgSpinner"></span>
                                    </button>
                                </div>
                                <div id="orgKraPinFeedback" class="form-feedback mt-1"></div>
                                <small class="form-text text-muted">Format: Letter followed by 9 digits and a letter (e.g. P123456789X)</small>
                            </div>

                            <div id="otherFieldsorg" style="display: none;">
                                <div class="form-group mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="orgName" name="orgName" placeholder=" " required>
                                        <label for="orgName">Organization Name <span class="text-danger">*</span></label>
                                        <div class="invalid-feedback">Please enter organization name</div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="orgEmail" name="orgEmail" placeholder=" " required>
                                            <label for="orgEmail">Organization Email <span class="text-danger">*</span></label>
                                            <div class="invalid-feedback">Please enter a valid email address</div>
                                            <div id="orgEmailFeedback" class="form-feedback">Please enter a valid email address</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="orgPhone" name="orgPhone" placeholder=" " required>
                                            <label for="orgPhone">Organization Phone <span class="text-danger">*</span></label>
                                            <div class="invalid-feedback">Please enter a valid phone number</div>
                                            <div id="orgPhoneFeedback" class="form-feedback">Please enter a 10-digit phone number</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5">
                            <button type="button" id="backBtn" class="btn btn-outline-secondary px-4 py-2">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <button type="button" id="nextBtn" class="btn btn-primary px-4 py-2" disabled>
                                Continue <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OTP Modal -->
<div class="modal fade otp-modal" id="otpModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Email Verification</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>We've sent a 6-digit verification code to <strong id="otpEmail"></strong>. Please enter it below:</p>
                <div class="form-group">
                    <input type="text" class="form-control form-control-lg text-center" id="otpInput" placeholder="••••••" maxlength="6">
                    <div id="otpError" class="form-feedback invalid"></div>
                </div>
                <p class="small text-muted">Didn't receive the code? <a href="#" id="resendOtpLink">Resend code</a></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="verifyOtpBtn">
                    Verify <i class="fas fa-check ms-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styling for the form */
    .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .form-section {
        border-left: 4px solid #d9232e;
        padding-left: 1rem;
        margin-bottom: 2rem;
    }

    .form-section h4 {
        color: #333;
        font-weight: 500;
    }

    .form-control {
        border: 1px solid #ced4da;
        transition: all 0.3s;
        height: calc(3.5rem + 2px);
        padding-top: 1.625rem;
    }

    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    .form-floating label {
        color: #6c757d;
        transition: all 0.2s;
    }

    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label {
        transform: scale(0.85) translateY(-0.8rem) translateX(0.15rem);
        opacity: 0.8;
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-primary {
        background-color: #d9232e;
        border-color: #d9232e;
    }

    .btn-primary:hover {
        background-color: #c51f29;
        border-color: #c51f29;
    }

    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
    }

    /* Form feedback styles */
    .form-feedback {
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: none;
    }
    
    .form-feedback.valid {
        color: #28a745;
        display: block;
    }
    
    .form-feedback.invalid {
        color: #dc3545;
        display: block;
    }

    .btn-spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top: 2px solid white;
        animation: spin 1s linear infinite;
        margin-left: 8px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Modal styles */
    .otp-modal .modal-header {
        border-radius: 0;
    }

    .otp-modal .modal-content {
        border-radius: 0;
        border: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
        
        #verifyPinBtn, #verifyKraPinBtn {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
</style>

<script>
    // Store token in localStorage if it came from session
    const token = '<?php echo $token; ?>';
    if (token && !localStorage.getItem('authToken')) {
        localStorage.setItem('authToken', token);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Check authentication and load saved data
        checkAuthAndLoadData();

        // Initialize form event listeners
        initFormEvents();
    });

    async function checkAuthAndLoadData() {
        try {
            const response = await fetch('api/validate-token.php', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                }
            });

            if (!response.ok) throw new Error('Invalid token');

            await fetchUserData();
            loadFormData();
        } catch (error) {
            console.error('Authentication error:', error);
            window.location.href = 'login.html';
        }
    }

    function loadFormData() {
        const storedData = localStorage.getItem('taxAgentInfo');
        if (storedData) {
            const formData = JSON.parse(storedData);

            // Set form values
            document.getElementById('userType').value = formData.userType || '';
            document.getElementById('surname').value = formData.surname || '';
            document.getElementById('othernames').value = formData.othernames || '';
            document.getElementById('email').value = formData.email || '';
            document.getElementById('phone').value = formData.phone || '';
            document.getElementById('kra_pin').value = formData.kra_pin || '';
            document.getElementById('orgName').value = formData.orgName || '';
            document.getElementById('orgPhone').value = formData.orgPhone || '';
            document.getElementById('orgEmail').value = formData.orgEmail || '';
            document.getElementById('orgKraPin').value = formData.orgKraPin || '';

            // Show appropriate sections
            toggleFormSections();
            checkFormCompletion();
        }
    }

    function initFormEvents() {
        // User type change handler
        document.getElementById('userType').addEventListener('change', toggleFormSections);

        // Input validation handlers
        document.getElementById('email').addEventListener('blur', validateEmail);
        document.getElementById('phone').addEventListener('blur', validatePhone);
        document.getElementById('orgEmail').addEventListener('blur', validateOrgEmail);
        document.getElementById('orgPhone').addEventListener('blur', validateOrgPhone);

        // Verify buttons
        document.getElementById('verifyPinBtn').addEventListener('click', verifyKraPin);
        document.getElementById('verifyKraPinBtn').addEventListener('click', verifyOrgKraPin);

        // Form navigation
        document.getElementById('backBtn').addEventListener('click', goBack);
        document.getElementById('nextBtn').addEventListener('click', proceedToOTP);

        // OTP handlers
        document.getElementById('verifyOtpBtn').addEventListener('click', verifyOTP);
        document.getElementById('resendOtpLink').addEventListener('click', resendOTP);

        // Real-time form validation
        document.querySelectorAll('#step3Form input').forEach(input => {
            input.addEventListener('input', checkFormCompletion);
        });
    }

    function toggleFormSections() {
        const userType = document.getElementById('userType').value;
        const individualFields = document.getElementById('individualFields');
        const organizationFields = document.getElementById('organizationFields');

        if (userType === 'organization') {
            individualFields.style.display = 'none';
            organizationFields.style.display = 'block';
        } else if (userType === 'individual') {
            individualFields.style.display = 'block';
            organizationFields.style.display = 'none';
        } else {
            individualFields.style.display = 'none';
            organizationFields.style.display = 'none';
        }

        checkFormCompletion();
    }

    function checkFormCompletion() {
        const userType = document.getElementById('userType').value;
        let allFieldsValid = true;

        if (userType === 'individual') {
            // Check KRA PIN verification
            const kraPinVerified = document.getElementById('otherFields').style.display !== 'none';

            // Check required fields
            const requiredFields = [
                document.getElementById('kra_pin'),
                document.getElementById('surname'),
                document.getElementById('othernames'),
                document.getElementById('email'),
                document.getElementById('phone')
            ];

            allFieldsValid = kraPinVerified && requiredFields.every(field => field.value.trim() !== '');
        } else if (userType === 'organization') {
            // Check Org KRA PIN verification
            const orgKraPinVerified = document.getElementById('otherFieldsorg').style.display !== 'none';

            // Check required fields
            const requiredFields = [
                document.getElementById('orgKraPin'),
                document.getElementById('orgName'),
                document.getElementById('orgEmail'),
                document.getElementById('orgPhone')
            ];

            allFieldsValid = orgKraPinVerified && requiredFields.every(field => field.value.trim() !== '');
        } else {
            allFieldsValid = false;
        }

        document.getElementById('nextBtn').disabled = !allFieldsValid;
    }

    // Validation functions
    function validateEmail() {
        const email = document.getElementById('email').value;
        const feedback = document.getElementById('emailFeedback');
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        if (email && !isValid) {
            feedback.textContent = "Please enter a valid email address";
            feedback.className = "form-feedback invalid";
            return false;
        } else if (isValid) {
            feedback.textContent = "Email address is valid";
            feedback.className = "form-feedback valid";
            return true;
        }
        return false;
    }

    function validatePhone() {
        const phone = document.getElementById('phone').value;
        const feedback = document.getElementById('phoneFeedback');
        const isValid = /^\d{10}$/.test(phone);

        if (phone && !isValid) {
            feedback.textContent = "Please enter a 10-digit phone number";
            feedback.className = "form-feedback invalid";
            return false;
        } else if (isValid) {
            feedback.textContent = "Phone number is valid";
            feedback.className = "form-feedback valid";
            return true;
        }
        return false;
    }

    function validateOrgEmail() {
        const email = document.getElementById('orgEmail').value;
        const feedback = document.getElementById('orgEmailFeedback');
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        if (email && !isValid) {
            feedback.textContent = "Please enter a valid email address";
            feedback.className = "form-feedback invalid";
            return false;
        } else if (isValid) {
            feedback.textContent = "Email address is valid";
            feedback.className = "form-feedback valid";
            return true;
        }
        return false;
    }

    function validateOrgPhone() {
        const phone = document.getElementById('orgPhone').value;
        const feedback = document.getElementById('orgPhoneFeedback');
        const isValid = /^\d{10}$/.test(phone);

        if (phone && !isValid) {
            feedback.textContent = "Please enter a 10-digit phone number";
            feedback.className = "form-feedback invalid";
            return false;
        } else if (isValid) {
            feedback.textContent = "Phone number is valid";
            feedback.className = "form-feedback valid";
            return true;
        }
        return false;
    }

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
                if (userData.kra_pin) {
                    document.getElementById('kra_pin').value = userData.kra_pin;
                    // Auto-verify if KRA PIN is already provided
                    verifyKraPin();
                }
                document.getElementById('surname').value = userData.surname || '';
                document.getElementById('othernames').value = userData.othernames || '';
                document.getElementById('email').value = userData.email || '';
                document.getElementById('phone').value = userData.phone || '';
            }
        } catch (error) {
            console.error('Error fetching user data:', error);
            showAlert('warning', 'Could not load your registration details. Please fill the form manually.');
        }
    }

    // Verification functions
    async function verifyKraPin() {
        const kraPin = document.getElementById('kra_pin').value;
        const feedback = document.getElementById('kraPinFeedback');
        const button = document.getElementById('verifyPinBtn');
        const spinner = document.getElementById('verifyPinSpinner');

        if (!kraPin) {
            feedback.textContent = "Please enter a KRA PIN";
            feedback.className = "form-feedback invalid";
            return;
        }

        try {
            button.disabled = true;
            spinner.style.display = 'inline-block';
            feedback.textContent = "Verifying...";
            feedback.className = "form-feedback";

            const response = await fetch('validate_kra_pin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                },
                body: 'kra_pin=' + encodeURIComponent(kraPin)
            });

            if (response.status === 401) {
                window.location.href = 'api/login.php?session_expired=1';
                return;
            }

            const data = await response.json();

            if (data.status === "success") {
                feedback.textContent = "KRA PIN verified successfully";
                feedback.className = "form-feedback valid";
                document.getElementById('otherFields').style.display = 'block';

                // Enable fields
                document.getElementById('surname').disabled = false;
                document.getElementById('othernames').disabled = false;
                document.getElementById('email').disabled = false;
                document.getElementById('phone').disabled = false;

                // If API returned name data, populate it
                if (data.surname) document.getElementById('surname').value = data.surname;
                if (data.othernames) document.getElementById('othernames').value = data.othernames;

                checkFormCompletion();
            } else {
                feedback.textContent = data.message || "Invalid KRA PIN. Please try again.";
                feedback.className = "form-feedback invalid";
            }
        } catch (error) {
            console.error('Verification error:', error);
            feedback.textContent = "An error occurred during verification";
            feedback.className = "form-feedback invalid";
        } finally {
            button.disabled = false;
            spinner.style.display = 'none';
        }
    }

    async function verifyOrgKraPin() {
        const kraPin = document.getElementById('orgKraPin').value;
        const feedback = document.getElementById('orgKraPinFeedback');
        const button = document.getElementById('verifyKraPinBtn');
        const spinner = document.getElementById('verifyOrgSpinner');

        if (!kraPin) {
            feedback.textContent = "Please enter a KRA PIN";
            feedback.className = "form-feedback invalid";
            return;
        }

        try {
            button.disabled = true;
            spinner.style.display = 'inline-block';
            feedback.textContent = "Verifying...";
            feedback.className = "form-feedback";

            const response = await fetch('validate_org_pin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                },
                body: 'orgKraPin=' + encodeURIComponent(kraPin)
            });

            if (response.status === 401) {
                window.location.href = 'api/login.php?session_expired=1';
                return;
            }

            const data = await response.json();

            if (data.status === "success") {
                feedback.textContent = "KRA PIN verified successfully";
                feedback.className = "form-feedback valid";
                document.getElementById('otherFieldsorg').style.display = 'block';

                // If API returned organization name, populate it
                if (data.orgName) document.getElementById('orgName').value = data.orgName;

                checkFormCompletion();
            } else {
                feedback.textContent = data.message || "Invalid KRA PIN. Please try again.";
                feedback.className = "form-feedback invalid";
            }
        } catch (error) {
            console.error('Verification error:', error);
            feedback.textContent = "An error occurred during verification";
            feedback.className = "form-feedback invalid";
        } finally {
            button.disabled = false;
            spinner.style.display = 'none';
        }
    }

    // Navigation functions
    function goBack() {
        saveFormData();
        window.location.href = 'options.php';
    }

    async function proceedToOTP() {
        if (document.getElementById('nextBtn').disabled) return;

        saveFormData();

        const userType = document.getElementById('userType').value;
        const email = userType === 'organization' ?
            document.getElementById('orgEmail').value :
            document.getElementById('email').value;

        document.getElementById('otpEmail').textContent = email;

        try {
            const response = await fetch('send_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                },
                body: 'email=' + encodeURIComponent(email)
            });

            if (response.status === 401) {
                window.location.href = 'api/login.php?session_expired=1';
                return;
            }

            const data = await response.json();

            if (data.status === "success") {
                const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
                otpModal.show();
            } else {
                showNotification('Error sending OTP: ' + (data.message || 'Please try again'), 'error');
            }
        } catch (error) {
            console.error('OTP error:', error);
            showNotification('An error occurred while sending OTP', 'error');
        }
    }

    async function verifyOTP() {
        const userType = document.getElementById('userType').value;
        const email = userType === 'organization' ?
            document.getElementById('orgEmail').value :
            document.getElementById('email').value;
        const otp = document.getElementById('otpInput').value;
        const errorElement = document.getElementById('otpError');

        if (!otp || otp.length !== 6) {
            errorElement.textContent = "Please enter a valid 6-digit code";
            return;
        }

        try {
            const response = await fetch('verify_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                },
                body: 'email=' + encodeURIComponent(email) + '&otp=' + encodeURIComponent(otp)
            });

            if (response.status === 401) {
                window.location.href = 'api/login.php?session_expired=1';
                return;
            }

            const data = await response.json();

            if (data.status === "success") {
                const otpModal = bootstrap.Modal.getInstance(document.getElementById('otpModal'));
                otpModal.hide();
                window.location.href = 'client.php';
            } else {
                errorElement.textContent = data.message || "Invalid OTP. Please try again.";
            }
        } catch (error) {
            console.error('OTP verification error:', error);
            errorElement.textContent = "An error occurred during verification";
        }
    }

    async function resendOTP(e) {
        e.preventDefault();

        const userType = document.getElementById('userType').value;
        const email = userType === 'organization' ?
            document.getElementById('orgEmail').value :
            document.getElementById('email').value;

        try {
            const response = await fetch('send_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                },
                body: 'email=' + encodeURIComponent(email)
            });

            if (response.status === 401) {
                window.location.href = 'api/login.php?session_expired=1';
                return;
            }

            const data = await response.json();

            if (data.status === "success") {
                showNotification('New OTP sent successfully', 'success');
            } else {
                showNotification('Error resending OTP: ' + (data.message || 'Please try again'), 'error');
            }
        } catch (error) {
            console.error('Resend OTP error:', error);
            showNotification('An error occurred while resending OTP', 'error');
        }
    }

    function saveFormData() {
        const formData = {
            userType: document.getElementById('userType').value,
            surname: document.getElementById('surname').value,
            othernames: document.getElementById('othernames').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            kra_pin: document.getElementById('kra_pin').value,
            orgName: document.getElementById('orgName').value,
            orgPhone: document.getElementById('orgPhone').value,
            orgEmail: document.getElementById('orgEmail').value,
            orgKraPin: document.getElementById('orgKraPin').value
        };
        localStorage.setItem('taxAgentInfo', JSON.stringify(formData));
    }

    function showNotification(message, type) {
        // Implement your notification system here
        alert(`${type.toUpperCase()}: ${message}`);
    }
</script>

<?php include 'footer.php'; ?>