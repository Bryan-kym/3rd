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

<div class="client-container">
    <div class="client-card">
        <div class="client-header">
            <h2>Client Information</h2>
            <p>Please fill in your client's information below.</p>
        </div>

        <!-- Client Information Form -->
        <form id="step3Form">
            <!-- Dropdown to select Individual or Organization -->
            <div class="form-group">
                <label for="userType2">Client Type</label>
                <select class="form-input" id="userType2" name="userType2" required>
                    <option value="individual">Individual</option>
                    <option value="organization">Organization</option>
                </select>
                <div class="form-feedback" id="userTypeFeedback"></div>
            </div>

            <!-- Fields for Individual -->
            <div id="individualFields">
                <div class="form-group" id="kraPinField">
                    <label for="kra_pin">KRA PIN</label>
                    <div class="input-group">
                        <input type="text" class="form-input" id="kra_pin" name="kra_pin" placeholder="Enter KRA PIN">
                        <button type="button" class="btn-primary" id="verifyPinBtn">
                            <span id="verifyPinText">Verify</span>
                            <span class="loading-spinner" id="verifyPinSpinner"></span>
                        </button>
                    </div>
                    <div class="form-feedback" id="kraPinFeedback"></div>
                </div>
                
                <div id="otherFields" class="additional-fields">
                    <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" class="form-input" id="surname" name="surname" required disabled>
                        <div class="form-feedback" id="surnameFeedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="othernames">Other Names</label>
                        <input type="text" class="form-input" id="othernames" name="othernames" required disabled>
                        <div class="form-feedback" id="othernamesFeedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-input" id="email" name="email" required disabled>
                        <div class="form-feedback" id="emailFeedback">Please enter a valid email address</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-input" id="phone" name="phone" required disabled>
                        <div class="form-feedback" id="phoneFeedback">Please enter a 10-digit phone number</div>
                    </div>
                </div>
            </div>

            <!-- Fields for Organization (Hidden initially) -->
            <div id="organizationFields" class="additional-fields" style="display: none;">
                <div class="form-group" id="orgKraPinField">
                    <label for="orgKraPin">Organization KRA PIN</label>
                    <div class="input-group">
                        <input type="text" class="form-input" id="orgKraPin" name="orgKraPin" placeholder="Enter KRA PIN">
                        <button type="button" class="btn-primary" id="verifyKraPinBtn">
                            <span id="verifyOrgText">Verify</span>
                            <span class="loading-spinner" id="verifyOrgSpinner"></span>
                        </button>
                    </div>
                    <div class="form-feedback" id="orgKraPinFeedback"></div>
                </div>
                
                <div id="otherFieldsorg" class="additional-fields" style="display: none;">
                    <div class="form-group">
                        <label for="orgName">Organization Name</label>
                        <input type="text" class="form-input" id="orgName" name="orgName" required disabled>
                        <div class="form-feedback" id="orgNameFeedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="orgPhone">Organization Phone</label>
                        <input type="tel" class="form-input" id="orgPhone" name="orgPhone" required disabled>
                        <div class="form-feedback" id="orgPhoneFeedback">Please enter a 10-digit phone number</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="orgEmail">Organization Email</label>
                        <input type="email" class="form-input" id="orgEmail" name="orgEmail" required disabled>
                        <div class="form-feedback" id="orgEmailFeedback">Please enter a valid email address</div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="form-actions">
                <button type="button" id="backBtn" class="btn-outline">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <button type="button" id="nextBtn" class="btn-primary" disabled>
                    Next <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    :root {
        --primary-color: #d9232e;
        --primary-light: rgba(217, 35, 46, 0.1);
        --secondary-color: #151515;
        --light-color: #ffffff;
        --grey-color: #6c757d;
        --light-grey: #f5f5f5;
        --dark-grey: #343a40;
        --success-color: #28a745;
        --error-color: #dc3545;
        --border-radius: 0.5rem;
        --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    .client-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 120px);
        padding: 2rem;
        background-color: var(--light-grey);
    }

    .client-card {
        background: var(--light-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 700px;
        padding: 2.5rem;
    }

    .client-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .client-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .client-header p {
        color: var(--grey-color);
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--secondary-color);
    }

    .input-group {
        display: flex;
        gap: 0.5rem;
    }

    .input-group .form-input {
        flex: 1;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        transition: var(--transition);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(217, 35, 46, 0.15);
    }

    .form-input.invalid {
        border-color: var(--error-color);
    }

    .form-input.valid {
        border-color: var(--success-color);
    }

    .form-input:disabled {
        background-color: var(--light-grey);
        cursor: not-allowed;
    }

    .form-feedback {
        font-size: 0.85rem;
        margin-top: 0.25rem;
        color: var(--grey-color);
    }

    .form-feedback.error {
        color: var(--error-color);
    }

    .form-feedback.success {
        color: var(--success-color);
    }

    .additional-fields {
        display: none;
        margin-top: 1.5rem;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
    }

    /* Button Styles */
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        border: none;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 8px rgba(217, 35, 46, 0.15);
    }

    .btn-primary:hover {
        background-color: #c11e27;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(217, 35, 46, 0.2);
    }

    .btn-primary:disabled {
        background-color: var(--grey-color);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-outline {
        background-color: transparent;
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-outline:hover {
        background-color: var(--primary-light);
        transform: translateY(-2px);
    }

    .loading-spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top: 3px solid white;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .client-card {
            padding: 1.5rem;
        }
        
        .input-group {
            flex-direction: column;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn-primary, .btn-outline {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
// Store token in localStorage if it came from session
const token = '<?php echo $token; ?>';
if (token && !localStorage.getItem('authToken')) {
    localStorage.setItem('authToken', token);
}

// Check if coming from proper flow by verifying required localStorage items
window.addEventListener('load', async function() {
    // Check for auth token and required flow items
    if (!localStorage.getItem('authToken') || 
        !localStorage.getItem('nda_form') || 
        !localStorage.getItem('selectedCategory') ||
        localStorage.getItem('selectedCategory') !== 'taxagent') {
        window.location.href = 'dashboard.php';
        return;
    }

    // Validate token with server
    try {
        const response = await fetch('api/validate-token.php', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('authToken')
            }
        });
        
        if (!response.ok) {
            localStorage.removeItem('authToken');
            localStorage.removeItem('nda_form');
            localStorage.removeItem('selectedCategory');
            localStorage.removeItem('taxAgentInfo');
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Token validation error:', error);
        window.location.href = 'login.html';
    }

    // Load any previously saved form data
    loadClientInfo();
});

// Function to toggle fields based on selected option
function toggleFields() {
    const userType = document.getElementById('userType2').value;
    document.getElementById('individualFields').style.display = userType === 'individual' ? 'block' : 'none';
    document.getElementById('organizationFields').style.display = userType === 'organization' ? 'block' : 'none';
    
    // Reset validation when switching types
    checkFormCompletion();
}

// Validation functions
function validateKraPin(pin) {
    const kraPinFeedback = document.getElementById('kraPinFeedback');
    const isValid = pin.trim().length > 0;

    if (pin) {
        if (isValid) {
            document.getElementById('kra_pin').classList.remove('invalid');
            document.getElementById('kra_pin').classList.add('valid');
            kraPinFeedback.textContent = "";
            kraPinFeedback.classList.remove('error');
        } else {
            document.getElementById('kra_pin').classList.remove('valid');
            document.getElementById('kra_pin').classList.add('invalid');
            kraPinFeedback.textContent = "Please enter a valid KRA PIN";
            kraPinFeedback.classList.add('error');
        }
    } else {
        document.getElementById('kra_pin').classList.remove('valid', 'invalid');
        kraPinFeedback.textContent = "Please enter KRA PIN";
        kraPinFeedback.classList.add('error');
    }

    return isValid;
}

function validateOrgKraPin(pin) {
    const orgKraPinFeedback = document.getElementById('orgKraPinFeedback');
    const isValid = pin.trim().length > 0;

    if (pin) {
        if (isValid) {
            document.getElementById('orgKraPin').classList.remove('invalid');
            document.getElementById('orgKraPin').classList.add('valid');
            orgKraPinFeedback.textContent = "";
            orgKraPinFeedback.classList.remove('error');
        } else {
            document.getElementById('orgKraPin').classList.remove('valid');
            document.getElementById('orgKraPin').classList.add('invalid');
            orgKraPinFeedback.textContent = "Please enter a valid KRA PIN";
            orgKraPinFeedback.classList.add('error');
        }
    } else {
        document.getElementById('orgKraPin').classList.remove('valid', 'invalid');
        orgKraPinFeedback.textContent = "Please enter KRA PIN";
        orgKraPinFeedback.classList.add('error');
    }

    return isValid;
}

function validateName(name, fieldId, feedbackId) {
    const feedback = document.getElementById(feedbackId);
    const isValid = name.trim().length > 0;

    if (name) {
        if (isValid) {
            document.getElementById(fieldId).classList.remove('invalid');
            document.getElementById(fieldId).classList.add('valid');
            feedback.textContent = "";
            feedback.classList.remove('error');
        } else {
            document.getElementById(fieldId).classList.remove('valid');
            document.getElementById(fieldId).classList.add('invalid');
            feedback.textContent = "This field is required";
            feedback.classList.add('error');
        }
    } else {
        document.getElementById(fieldId).classList.remove('valid', 'invalid');
        feedback.textContent = "This field is required";
        feedback.classList.add('error');
    }

    return isValid;
}

function validateEmail(email, fieldId, feedbackId) {
    const feedback = document.getElementById(feedbackId);
    const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    const isValid = regex.test(email);

    if (email) {
        if (isValid) {
            document.getElementById(fieldId).classList.remove('invalid');
            document.getElementById(fieldId).classList.add('valid');
            feedback.textContent = "Email address is valid";
            feedback.classList.remove('error');
            feedback.classList.add('success');
        } else {
            document.getElementById(fieldId).classList.remove('valid');
            document.getElementById(fieldId).classList.add('invalid');
            feedback.textContent = "Please enter a valid email address";
            feedback.classList.remove('success');
            feedback.classList.add('error');
        }
    } else {
        document.getElementById(fieldId).classList.remove('valid', 'invalid');
        feedback.textContent = "Please enter a valid email address";
        feedback.classList.remove('success');
        feedback.classList.add('error');
    }

    return isValid;
}

function validatePhone(phone, fieldId, feedbackId) {
    const feedback = document.getElementById(feedbackId);
    const regex = /^\d{10}$/;
    const isValid = regex.test(phone);

    if (phone) {
        if (isValid) {
            document.getElementById(fieldId).classList.remove('invalid');
            document.getElementById(fieldId).classList.add('valid');
            feedback.textContent = "Phone number is valid";
            feedback.classList.remove('error');
            feedback.classList.add('success');
        } else {
            document.getElementById(fieldId).classList.remove('valid');
            document.getElementById(fieldId).classList.add('invalid');
            feedback.textContent = "Please enter a 10-digit phone number";
            feedback.classList.remove('success');
            feedback.classList.add('error');
        }
    } else {
        document.getElementById(fieldId).classList.remove('valid', 'invalid');
        feedback.textContent = "Please enter a 10-digit phone number";
        feedback.classList.remove('success');
        feedback.classList.add('error');
    }

    return isValid;
}

// Function to check if all required fields are filled
function checkFormCompletion() {
    const userType = document.getElementById('userType2').value;
    let allValid = true;

    if (userType === 'individual') {
        const kraPinValid = validateKraPin(document.getElementById('kra_pin').value);
        const surnameValid = validateName(document.getElementById('surname').value, 'surname', 'surnameFeedback');
        const othernamesValid = validateName(document.getElementById('othernames').value, 'othernames', 'othernamesFeedback');
        const emailValid = validateEmail(document.getElementById('email').value, 'email', 'emailFeedback');
        const phoneValid = validatePhone(document.getElementById('phone').value, 'phone', 'phoneFeedback');
        
        allValid = kraPinValid && surnameValid && othernamesValid && emailValid && phoneValid;
    } else {
        const orgKraPinValid = validateOrgKraPin(document.getElementById('orgKraPin').value);
        const orgNameValid = validateName(document.getElementById('orgName').value, 'orgName', 'orgNameFeedback');
        const orgEmailValid = validateEmail(document.getElementById('orgEmail').value, 'orgEmail', 'orgEmailFeedback');
        const orgPhoneValid = validatePhone(document.getElementById('orgPhone').value, 'orgPhone', 'orgPhoneFeedback');
        
        allValid = orgKraPinValid && orgNameValid && orgEmailValid && orgPhoneValid;
    }

    document.getElementById('nextBtn').disabled = !allValid;
    return allValid;
}

// Function to store data in localStorage
function saveClientInfo() {
    const userType = document.getElementById('userType2').value;
    let clientInfo = {
        userType
    };

    if (userType === 'individual') {
        clientInfo.surname = document.getElementById('surname').value;
        clientInfo.othernames = document.getElementById('othernames').value;
        clientInfo.email = document.getElementById('email').value;
        clientInfo.phone = document.getElementById('phone').value;
        clientInfo.kra_pin = document.getElementById('kra_pin').value;
    } else {
        clientInfo.orgName = document.getElementById('orgName').value;
        clientInfo.orgPhone = document.getElementById('orgPhone').value;
        clientInfo.orgEmail = document.getElementById('orgEmail').value;
        clientInfo.orgKraPin = document.getElementById('orgKraPin').value;
    }

    localStorage.setItem('clientInfo', JSON.stringify(clientInfo));
}

// Function to load stored data into form fields
function loadClientInfo() {
    const savedData = localStorage.getItem('clientInfo');
    if (savedData) {
        const clientInfo = JSON.parse(savedData);
        document.getElementById('userType2').value = clientInfo.userType || 'individual';
        toggleFields();

        if (clientInfo.userType === 'individual') {
            document.getElementById('surname').value = clientInfo.surname || '';
            document.getElementById('othernames').value = clientInfo.othernames || '';
            document.getElementById('email').value = clientInfo.email || '';
            document.getElementById('phone').value = clientInfo.phone || '';
            document.getElementById('kra_pin').value = clientInfo.kra_pin || '';
            
            // Show other fields if KRA PIN exists
            if (clientInfo.kra_pin) {
                document.getElementById('otherFields').style.display = 'block';
                const otherFields = document.querySelectorAll('#otherFields input');
                otherFields.forEach(field => field.disabled = false);
            }
        } else {
            document.getElementById('orgName').value = clientInfo.orgName || '';
            document.getElementById('orgPhone').value = clientInfo.orgPhone || '';
            document.getElementById('orgEmail').value = clientInfo.orgEmail || '';
            document.getElementById('orgKraPin').value = clientInfo.orgKraPin || '';
            
            // Show other fields if KRA PIN exists
            if (clientInfo.orgKraPin) {
                document.getElementById('otherFieldsorg').style.display = 'block';
                const otherFields = document.querySelectorAll('#otherFieldsorg input');
                otherFields.forEach(field => field.disabled = false);
            }
        }
        checkFormCompletion();
    }
}

// Event listeners
document.getElementById('userType2').addEventListener('change', toggleFields);

// Auto-check required fields
document.querySelectorAll('input').forEach(field => {
    field.addEventListener('input', checkFormCompletion);
});

// KRA PIN verification for individuals
document.getElementById('verifyPinBtn').addEventListener('click', function() {
    const kraPin = document.getElementById('kra_pin').value;
    const kraPinFeedback = document.getElementById('kraPinFeedback');
    const verifyBtn = document.getElementById('verifyPinBtn');
    const verifyText = document.getElementById('verifyPinText');
    const verifySpinner = document.getElementById('verifyPinSpinner');

    if (!validateKraPin(kraPin)) {
        return;
    }

    // Show loading state
    verifyBtn.disabled = true;
    verifyText.textContent = "Verifying...";
    verifySpinner.style.display = 'inline-block';

    fetch('validate_kra_pin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
        },
        body: 'kra_pin=' + encodeURIComponent(kraPin)
    })
    .then(response => {
        if (response.status === 401) {
            window.location.href = 'api/login.php?session_expired=1';
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data.status === "success") {
            kraPinFeedback.textContent = "KRA PIN verified successfully";
            kraPinFeedback.classList.remove('error');
            kraPinFeedback.classList.add('success');
            
            document.getElementById('otherFields').style.display = 'block';
            const otherFields = document.querySelectorAll('#otherFields input');
            otherFields.forEach(field => field.disabled = false);
            
            // If we got name from the API, populate it
            if (data.surname) {
                document.getElementById('surname').value = data.surname;
            }
            if (data.othernames) {
                document.getElementById('othernames').value = data.othernames;
            }
            
            checkFormCompletion();
        } else {
            kraPinFeedback.textContent = data.message || "Invalid KRA PIN. Please try again.";
            kraPinFeedback.classList.remove('success');
            kraPinFeedback.classList.add('error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        kraPinFeedback.textContent = "An error occurred. Please try again.";
        kraPinFeedback.classList.remove('success');
        kraPinFeedback.classList.add('error');
    })
    .finally(() => {
        verifyBtn.disabled = false;
        verifyText.textContent = "Verify";
        verifySpinner.style.display = 'none';
    });
});

// KRA PIN verification for organizations
document.getElementById('verifyKraPinBtn').addEventListener('click', function() {
    const kraPin = document.getElementById('orgKraPin').value;
    const kraPinFeedback = document.getElementById('orgKraPinFeedback');
    const verifyBtn = document.getElementById('verifyKraPinBtn');
    const verifyText = document.getElementById('verifyOrgText');
    const verifySpinner = document.getElementById('verifyOrgSpinner');

    if (!validateOrgKraPin(kraPin)) {
        return;
    }

    // Show loading state
    verifyBtn.disabled = true;
    verifyText.textContent = "Verifying...";
    verifySpinner.style.display = 'inline-block';

    fetch('validate_org_pin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
        },
        body: 'orgKraPin=' + encodeURIComponent(kraPin)
    })
    .then(response => {
        if (response.status === 401) {
            window.location.href = 'api/login.php?session_expired=1';
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data.status === "success") {
            kraPinFeedback.textContent = "KRA PIN verified successfully";
            kraPinFeedback.classList.remove('error');
            kraPinFeedback.classList.add('success');
            
            document.getElementById('otherFieldsorg').style.display = 'block';
            const otherFields = document.querySelectorAll('#otherFieldsorg input');
            otherFields.forEach(field => field.disabled = false);
            
            // If we got organization name from the API, populate it
            if (data.orgName) {
                document.getElementById('orgName').value = data.orgName;
            }
            
            checkFormCompletion();
        } else {
            kraPinFeedback.textContent = data.message || "Invalid KRA PIN. Please try again.";
            kraPinFeedback.classList.remove('success');
            kraPinFeedback.classList.add('error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        kraPinFeedback.textContent = "An error occurred. Please try again.";
        kraPinFeedback.classList.remove('success');
        kraPinFeedback.classList.add('error');
    })
    .finally(() => {
        verifyBtn.disabled = false;
        verifyText.textContent = "Verify";
        verifySpinner.style.display = 'none';
    });
});

// Next button handler
document.getElementById('nextBtn').addEventListener('click', function() {
    if (!checkFormCompletion()) {
        return;
    }
    saveClientInfo();
    window.location.href = 'data_request.php';
});

// Back button handler
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'taxagent.php';
});

// Load data on page load
document.addEventListener('DOMContentLoaded', loadClientInfo);
</script>

<?php include 'footer.php'; ?>