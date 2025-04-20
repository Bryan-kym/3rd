<?php
ob_start(); // Start output buffering
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

<div class="organization-container">
    <div class="organization-card">
        <div class="organization-header">
            <h2>Organization Information</h2>
            <p>Please provide the details of your organization.</p>
        </div>

        <!-- Organization Information Form -->
        <form id="orgInfoForm">
            <div class="form-group" id="kraPinField">
                <label for="orgKraPin">Organization KRA PIN</label>
                <div class="input-group">
                    <input type="text" class="form-input" id="orgKraPin" name="orgKraPin" placeholder="Enter KRA PIN">
                    <button type="button" class="btn-primary" id="verifyKraPinBtn">
                        <span id="verifyText">Verify</span>
                        <span class="loading-spinner" id="verifySpinner"></span>
                    </button>
                </div>
                <div class="form-feedback" id="kraPinFeedback"></div>
            </div>
            
            <div id="otherFields" class="additional-fields">
                <div class="form-group">
                    <label for="orgName">Organization Name</label>
                    <input type="text" class="form-input" id="orgName" required disabled>
                    <div class="form-feedback" id="nameFeedback"></div>
                </div>
                
                <div class="form-group">
                    <label for="orgPhone">Organization Phone Number</label>
                    <input type="tel" class="form-input" id="orgPhone" required disabled>
                    <div class="form-feedback" id="phoneFeedback">Please enter a 10-digit phone number</div>
                </div>
                
                <div class="form-group">
                    <label for="orgEmail">Organization Email</label>
                    <input type="email" class="form-input" id="orgEmail" required disabled>
                    <div class="form-feedback" id="emailFeedback">Please enter a valid email address</div>
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

    .organization-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 120px);
        padding: 2rem;
        background-color: var(--light-grey);
    }

    .organization-card {
        background: var(--light-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 600px;
        padding: 2.5rem;
    }

    .organization-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .organization-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .organization-header p {
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
        .organization-card {
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
        (localStorage.getItem('selectedCategory') !== 'privatecompany' && 
         localStorage.getItem('selectedCategory') !== 'publiccompany')) {
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
            localStorage.removeItem('personalInfo');
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Token validation error:', error);
        window.location.href = 'login.html';
    }

    // Load any previously saved form data
    loadOrgDetails();
});

// Function to load saved organization details
function loadOrgDetails() {
    let orgDetails = JSON.parse(localStorage.getItem('org_details')) || {};

    document.getElementById('orgName').value = orgDetails.orgName || '';
    document.getElementById('orgPhone').value = orgDetails.orgPhone || '';
    document.getElementById('orgEmail').value = orgDetails.orgEmail || '';
    document.getElementById('orgKraPin').value = orgDetails.orgKraPin || '';

    // If we already have a KRA PIN, show the other fields
    if (orgDetails.orgKraPin) {
        document.getElementById('otherFields').style.display = 'block';
        const otherFields = document.querySelectorAll('#otherFields input');
        otherFields.forEach(field => field.disabled = false);
    }
    
    checkFormCompletion();
}

// Form validation functions
function validateKraPin(pin) {
    const kraPinFeedback = document.getElementById('kraPinFeedback');
    const isValid = pin.trim().length > 0;

    if (pin) {
        if (isValid) {
            document.getElementById('orgKraPin').classList.remove('invalid');
            document.getElementById('orgKraPin').classList.add('valid');
            kraPinFeedback.textContent = "";
            kraPinFeedback.classList.remove('error');
        } else {
            document.getElementById('orgKraPin').classList.remove('valid');
            document.getElementById('orgKraPin').classList.add('invalid');
            kraPinFeedback.textContent = "Please enter a valid KRA PIN";
            kraPinFeedback.classList.add('error');
        }
    } else {
        document.getElementById('orgKraPin').classList.remove('valid', 'invalid');
        kraPinFeedback.textContent = "Please enter KRA PIN";
        kraPinFeedback.classList.add('error');
    }

    return isValid;
}

function validateName(name) {
    const nameFeedback = document.getElementById('nameFeedback');
    const isValid = name.trim().length > 0;

    if (name) {
        if (isValid) {
            document.getElementById('orgName').classList.remove('invalid');
            document.getElementById('orgName').classList.add('valid');
            nameFeedback.textContent = "";
            nameFeedback.classList.remove('error');
        } else {
            document.getElementById('orgName').classList.remove('valid');
            document.getElementById('orgName').classList.add('invalid');
            nameFeedback.textContent = "Please enter organization name";
            nameFeedback.classList.add('error');
        }
    } else {
        document.getElementById('orgName').classList.remove('valid', 'invalid');
        nameFeedback.textContent = "Please enter organization name";
        nameFeedback.classList.add('error');
    }

    return isValid;
}

function validateEmail(email) {
    const emailFeedback = document.getElementById('emailFeedback');
    const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    const isValid = regex.test(email);

    if (email) {
        if (isValid) {
            document.getElementById('orgEmail').classList.remove('invalid');
            document.getElementById('orgEmail').classList.add('valid');
            emailFeedback.textContent = "Email address is valid";
            emailFeedback.classList.remove('error');
            emailFeedback.classList.add('success');
        } else {
            document.getElementById('orgEmail').classList.remove('valid');
            document.getElementById('orgEmail').classList.add('invalid');
            emailFeedback.textContent = "Please enter a valid email address";
            emailFeedback.classList.remove('success');
            emailFeedback.classList.add('error');
        }
    } else {
        document.getElementById('orgEmail').classList.remove('valid', 'invalid');
        emailFeedback.textContent = "Please enter a valid email address";
        emailFeedback.classList.remove('success');
        emailFeedback.classList.add('error');
    }

    return isValid;
}

function validatePhone(phone) {
    const phoneFeedback = document.getElementById('phoneFeedback');
    const regex = /^\d{10}$/;
    const isValid = regex.test(phone);

    if (phone) {
        if (isValid) {
            document.getElementById('orgPhone').classList.remove('invalid');
            document.getElementById('orgPhone').classList.add('valid');
            phoneFeedback.textContent = "Phone number is valid";
            phoneFeedback.classList.remove('error');
            phoneFeedback.classList.add('success');
        } else {
            document.getElementById('orgPhone').classList.remove('valid');
            document.getElementById('orgPhone').classList.add('invalid');
            phoneFeedback.textContent = "Please enter a 10-digit phone number";
            phoneFeedback.classList.remove('success');
            phoneFeedback.classList.add('error');
        }
    } else {
        document.getElementById('orgPhone').classList.remove('valid', 'invalid');
        phoneFeedback.textContent = "Please enter a 10-digit phone number";
        phoneFeedback.classList.remove('success');
        phoneFeedback.classList.add('error');
    }

    return isValid;
}

// Check form completion
function checkFormCompletion() {
    const kraPinValid = validateKraPin(document.getElementById('orgKraPin').value);
    const nameValid = validateName(document.getElementById('orgName').value);
    const emailValid = validateEmail(document.getElementById('orgEmail').value);
    const phoneValid = validatePhone(document.getElementById('orgPhone').value);
    
    const allValid = kraPinValid && nameValid && emailValid && phoneValid;
    document.getElementById('nextBtn').disabled = !allValid;
    
    return allValid;
}

// Event listeners for real-time validation
document.getElementById('orgKraPin').addEventListener('input', function() {
    validateKraPin(this.value);
});

document.getElementById('orgName').addEventListener('input', function() {
    validateName(this.value);
    checkFormCompletion();
});

document.getElementById('orgEmail').addEventListener('input', function() {
    validateEmail(this.value);
    checkFormCompletion();
});

document.getElementById('orgPhone').addEventListener('input', function() {
    validatePhone(this.value);
    checkFormCompletion();
});

// KRA PIN verification
document.getElementById('verifyKraPinBtn').addEventListener('click', function() {
    const kraPin = document.getElementById('orgKraPin').value;
    const kraPinFeedback = document.getElementById('kraPinFeedback');
    const verifyBtn = document.getElementById('verifyKraPinBtn');
    const verifyText = document.getElementById('verifyText');
    const verifySpinner = document.getElementById('verifySpinner');

    if (!validateKraPin(kraPin)) {
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
            
            document.getElementById('otherFields').style.display = 'block';
            const otherFields = document.querySelectorAll('#otherFields input');
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

// Save organization details and move to the next page
document.getElementById('nextBtn').addEventListener('click', function() {
    if (!checkFormCompletion()) {
        return;
    }

    let orgDetails = {
        orgName: document.getElementById('orgName').value.trim(),
        orgPhone: document.getElementById('orgPhone').value.trim(),
        orgEmail: document.getElementById('orgEmail').value.trim(),
        orgKraPin: document.getElementById('orgKraPin').value.trim()
    };

    localStorage.setItem('org_details', JSON.stringify(orgDetails));
    window.location.href = 'data_request.php';
});

// Back button navigation
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'personal_information.php';
});

// Load data on page load
document.addEventListener('DOMContentLoaded', loadOrgDetails);
</script>

<?php include 'footer.php'; 
ob_flush();?>