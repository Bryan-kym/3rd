<?php include 'header.php'; ?>

<div class="institution-container">
    <div class="institution-card">
        <div class="institution-header">
            <h2>Institution Details</h2>
            <p>Please fill in your institution's details below.</p>
        </div>

        <!-- Institution Details Form -->
        <form id="institutionForm">
            <div class="form-group">
                <label for="inst_name">Institution Name</label>
                <input type="text" class="form-input" id="inst_name" name="inst_name" required>
                <div class="form-feedback" id="nameFeedback"></div>
            </div>

            <div class="form-group">
                <label for="inst_email">Institution Email</label>
                <input type="email" class="form-input" id="inst_email" name="inst_email" required>
                <div class="form-feedback" id="emailFeedback">Please enter a valid email address.</div>
            </div>

            <div class="form-group">
                <label for="inst_phone">Institution Phone Number</label>
                <input type="tel" class="form-input" id="inst_phone" name="inst_phone" required>
                <div class="form-feedback" id="phoneFeedback">Please enter a 10-digit phone number.</div>
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

    .institution-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 120px);
        padding: 2rem;
        background-color: var(--light-grey);
    }

    .institution-card {
        background: var(--light-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 600px;
        padding: 2.5rem;
    }

    .institution-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .institution-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .institution-header p {
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

    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
    }

    /* Button Styles */
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
        padding: 0.75rem 1.75rem;
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
        padding: 0.75rem 1.75rem;
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

    @media (max-width: 768px) {
        .institution-card {
            padding: 1.5rem;
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
// Function to load saved institution details
function loadInstitutionDetails() {
    let insDetails = JSON.parse(localStorage.getItem('ins_details')) || {};

    document.getElementById('inst_name').value = insDetails.inst_name || '';
    document.getElementById('inst_email').value = insDetails.inst_email || '';
    document.getElementById('inst_phone').value = insDetails.inst_phone || '';

    // Validate fields if they have values
    if (insDetails.inst_email) validateEmail(insDetails.inst_email);
    if (insDetails.inst_phone) validatePhone(insDetails.inst_phone);
    
    checkFormCompletion();
}

// Form validation functions
function validateEmail(email) {
    const emailInput = document.getElementById('inst_email');
    const emailFeedback = document.getElementById('emailFeedback');
    const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    const isValid = regex.test(email);

    if (email) {
        if (isValid) {
            emailInput.classList.remove('invalid');
            emailInput.classList.add('valid');
            emailFeedback.textContent = "Email address is valid.";
            emailFeedback.classList.remove('error');
            emailFeedback.classList.add('success');
        } else {
            emailInput.classList.remove('valid');
            emailInput.classList.add('invalid');
            emailFeedback.textContent = "Please enter a valid email address.";
            emailFeedback.classList.remove('success');
            emailFeedback.classList.add('error');
        }
    } else {
        emailInput.classList.remove('valid', 'invalid');
        emailFeedback.textContent = "Please enter a valid email address.";
        emailFeedback.classList.remove('success');
        emailFeedback.classList.add('error');
    }

    return isValid;
}

function validatePhone(phone) {
    const phoneInput = document.getElementById('inst_phone');
    const phoneFeedback = document.getElementById('phoneFeedback');
    const regex = /^\d{10}$/;
    const isValid = regex.test(phone);

    if (phone) {
        if (isValid) {
            phoneInput.classList.remove('invalid');
            phoneInput.classList.add('valid');
            phoneFeedback.textContent = "Phone number is valid.";
            phoneFeedback.classList.remove('error');
            phoneFeedback.classList.add('success');
        } else {
            phoneInput.classList.remove('valid');
            phoneInput.classList.add('invalid');
            phoneFeedback.textContent = "Please enter a valid 10-digit phone number.";
            phoneFeedback.classList.remove('success');
            phoneFeedback.classList.add('error');
        }
    } else {
        phoneInput.classList.remove('valid', 'invalid');
        phoneFeedback.textContent = "Please enter a 10-digit phone number.";
        phoneFeedback.classList.remove('success');
        phoneFeedback.classList.add('error');
    }

    return isValid;
}

function validateName(name) {
    const nameInput = document.getElementById('inst_name');
    const nameFeedback = document.getElementById('nameFeedback');
    const isValid = name.trim().length > 0;

    if (name) {
        if (isValid) {
            nameInput.classList.remove('invalid');
            nameInput.classList.add('valid');
            nameFeedback.textContent = "";
            nameFeedback.classList.remove('error');
        } else {
            nameInput.classList.remove('valid');
            nameInput.classList.add('invalid');
            nameFeedback.textContent = "Please enter institution name.";
            nameFeedback.classList.add('error');
        }
    } else {
        nameInput.classList.remove('valid', 'invalid');
        nameFeedback.textContent = "Please enter institution name.";
        nameFeedback.classList.add('error');
    }

    return isValid;
}

// Check form completion
function checkFormCompletion() {
    const nameValid = validateName(document.getElementById('inst_name').value);
    const emailValid = validateEmail(document.getElementById('inst_email').value);
    const phoneValid = validatePhone(document.getElementById('inst_phone').value);
    
    const allValid = nameValid && emailValid && phoneValid;
    document.getElementById('nextBtn').disabled = !allValid;
    
    return allValid;
}

// Event listeners
document.getElementById('inst_name').addEventListener('input', function() {
    validateName(this.value);
    checkFormCompletion();
});

document.getElementById('inst_email').addEventListener('input', function() {
    validateEmail(this.value);
    checkFormCompletion();
});

document.getElementById('inst_phone').addEventListener('input', function() {
    validatePhone(this.value);
    checkFormCompletion();
});

// Save institution details and move to the next page
document.getElementById('nextBtn').addEventListener('click', function() {
    if (!checkFormCompletion()) {
        return;
    }

    let insDetails = {
        inst_name: document.getElementById('inst_name').value.trim(),
        inst_email: document.getElementById('inst_email').value.trim(),
        inst_phone: document.getElementById('inst_phone').value.trim()
    };

    localStorage.setItem('ins_details', JSON.stringify(insDetails));
    window.location.href = 'data_request.php';
});

// Back button navigation
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'personal_information.php';
});

// Load data on page load
document.addEventListener('DOMContentLoaded', loadInstitutionDetails);
</script>

<?php include 'footer.php'; ?>