<?php
include 'header.php'; 
require_once 'auth.php';

try {
    $userId = authenticate();    
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : 
             (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
} catch (Exception $e) {
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>

<style>
    .form-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .form-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .form-header h2 {
        color: #d9232e;
        font-weight: 600;
    }
    
    .form-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f9f9f9;
        border-radius: 8px;
        border-left: 4px solid #d9232e;
    }
    
    .form-section h4 {
        color: #333;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    
    .input-group-verify .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        min-width: 100px;
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }
    
    .btn-primary {
        background-color: #d9232e;
        border-color: #d9232e;
    }
    
    .btn-primary:hover {
        background-color: #a51b24;
        border-color: #a51b24;
    }
    
    .btn-outline-primary {
        color: #d9232e;
        border-color: #d9232e;
    }
    
    .btn-outline-primary:hover {
        background-color: #d9232e;
        color: white;
    }
    
    .form-feedback {
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }
    
    .form-feedback.valid {
        color: #28a745;
    }
    
    .form-feedback.invalid {
        color: #dc3545;
    }
    
    .otp-modal .modal-header {
        background-color: #d9232e;
        color: white;
        border-radius: 0;
    }
    
    .otp-modal .modal-content {
        border-radius: 0;
    }
    
    .otp-modal .modal-footer {
        justify-content: space-between;
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
    
    .additional-fields {
        transition: all 0.3s ease;
        overflow: hidden;
    }
</style>

<div class="form-container">
    <div class="form-header">
        <h2>Tax Agent Registration</h2>
        <p class="text-muted">Complete your profile information to proceed</p>
    </div>
    
    <form id="step3Form">
        <div class="form-section">
            <h4>Account Type</h4>
            <div class="form-group">
                <label for="userType">I am registering as:</label>
                <select class="form-control" id="userType" name="userType" required>
                    <option value="">Select account type</option>
                    <option value="individual">Individual Tax Agent</option>
                    <option value="organization">Organization Representative</option>
                </select>
            </div>
        </div>
        
        <div id="individualFields" class="form-section additional-fields" style="display: block;">
            <h4>Personal Information</h4>
            
            <div class="form-group">
                <label for="kra_pin">KRA PIN</label>
                <div class="input-group input-group-verify">
                    <input type="text" class="form-control" id="kra_pin" name="kra_pin" placeholder="A123456789X">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" id="verifyPinBtn">
                            Verify
                            <span class="btn-spinner" id="verifyPinSpinner"></span>
                        </button>
                    </div>
                </div>
                <div id="kraPinFeedback" class="form-feedback"></div>
            </div>
            
            <div id="otherFields" class="additional-fields" style="display: block;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="surname">Surname</label>
                            <input type="text" class="form-control" id="surname" name="surname" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="othernames">Other Names</label>
                            <input type="text" class="form-control" id="othernames" name="othernames" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div id="emailFeedback" class="form-feedback">Please enter a valid email address</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                            <div id="phoneFeedback" class="form-feedback">Please enter a 10-digit phone number</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="organizationFields" class="form-section additional-fields" style="display: none;">
            <h4>Organization Information</h4>
            
            <div class="form-group">
                <label for="orgKraPin">Organization KRA PIN</label>
                <div class="input-group input-group-verify">
                    <input type="text" class="form-control" id="orgKraPin" name="orgKraPin" placeholder="P123456789X">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" id="verifyKraPinBtn">
                            Verify
                            <span class="btn-spinner" id="verifyOrgSpinner"></span>
                        </button>
                    </div>
                </div>
                <div id="orgKraPinFeedback" class="form-feedback"></div>
            </div>
            
            <div id="otherFieldsorg" class="additional-fields" style="display: none;">
                <div class="form-group">
                    <label for="orgName">Organization Name</label>
                    <input type="text" class="form-control" id="orgName" name="orgName" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="orgEmail">Organization Email</label>
                            <input type="email" class="form-control" id="orgEmail" name="orgEmail" required>
                            <div id="orgEmailFeedback" class="form-feedback">Please enter a valid email address</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="orgPhone">Organization Phone</label>
                            <input type="tel" class="form-control" id="orgPhone" name="orgPhone" required>
                            <div id="orgPhoneFeedback" class="form-feedback">Please enter a 10-digit phone number</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" id="backBtn" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </button>
            <button type="button" id="nextBtn" class="btn btn-primary" disabled>
                Continue <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>

<div class="modal fade otp-modal" id="otpModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Verification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="verifyOtpBtn">
                    Verify <i class="fas fa-check ml-2"></i>
                </button>
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

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication and load saved data
    checkAuthAndLoadData();
    
    // Initialize form event listeners
    initFormEvents();
});

async function checkAuthAndLoadData() {
    try {
        const response = await fetch('api/validate-token.php', {
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('authToken') }
        });
        
        if (!response.ok) throw new Error('Invalid token');
        
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
    } 
    else if (userType === 'organization') {
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
    }
    else {
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
    const email = userType === 'organization' 
        ? document.getElementById('orgEmail').value 
        : document.getElementById('email').value;
    
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
            $('#otpModal').modal('show');
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
    const email = userType === 'organization' 
        ? document.getElementById('orgEmail').value 
        : document.getElementById('email').value;
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
            $('#otpModal').modal('hide');
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
    const email = userType === 'organization' 
        ? document.getElementById('orgEmail').value 
        : document.getElementById('email').value;
    
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