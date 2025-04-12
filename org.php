<?php 
include 'header.php'; 
require_once 'auth.php'; // Include your authentication functions

// Check if user is authenticated
try {
    $userId = authenticate(); // This will redirect if not authenticated
    
    // Get token from session or headers
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : 
             (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
} catch (Exception $e) {
    // Redirect to login if not authenticated
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>

<div class="container mt-5 w-50">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Organization Information</h3>
            <p>Please provide the details of your organization.</p>

            <!-- Organization Information Form -->
            <form id="orgInfoForm">
                <div class="form-group" id="kraPinField">
                    <label for="orgKraPin">Organization KRA PIN</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="orgKraPin" name="orgKraPin">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" id="verifyKraPinBtn">Verify</button>
                        </div>
                    </div>
                    <p id="kraPinError" class="text-danger"></p>
                </div>
                <div id="otherFields" style="display: none;">
                    <div class="form-group">
                        <label for="orgName">Organization Name:</label>
                        <input type="text" class="form-control" id="orgName" required disabled>
                    </div>
                    <div class="form-group">
                        <label for="orgPhone">Organization Phone Number:</label>
                        <input type="tel" class="form-control" id="orgPhone" required disabled>
                        <small id="phoneHelp" class="form-text text-muted">Please enter a 10-digit phone number.</small>
                    </div>
                    <div class="form-group">
                        <label for="orgEmail">Organization Email:</label>
                        <input type="email" class="form-control" id="orgEmail" required disabled>
                        <small id="emailHelp" class="form-text text-muted">Please enter a valid email address.</small>
                    </div>
                </div>
                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary mt-4">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right mt-4" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

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

    checkFormCompletion(); // Ensure button state is updated after loading
}

// Enable 'Next' button when all required fields are filled
const requiredFields = document.querySelectorAll('#orgInfoForm input[required]');
const nextBtn = document.getElementById('nextBtn');

function checkFormCompletion() {
    let allFilled = Array.from(requiredFields).every(field => field.value.trim() !== '');
    nextBtn.disabled = !allFilled;
    return allFilled;
}

// Attach input event listeners for validation
requiredFields.forEach(field => {
    field.addEventListener('input', checkFormCompletion);
});

// KRA PIN verification
document.getElementById('verifyKraPinBtn').addEventListener('click', function() {
    const kraPin = document.getElementById('orgKraPin').value;
    const kraPinError = document.getElementById('kraPinError');

    if (!kraPin) {
        kraPinError.innerText = "Please enter a KRA PIN.";
        return;
    }

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
            kraPinError.innerText = "";
            document.getElementById('otherFields').style.display = 'block';
            const otherFields = document.querySelectorAll('#otherFields input');
            otherFields.forEach(field => field.disabled = false);
            checkFormCompletion();
        } else {
            kraPinError.innerText = data.message || "Invalid KRA PIN. Please try again.";
        }
    })
    .catch(error => {
        console.error('Error:', error);
        kraPinError.innerText = "An error occurred. Please try again.";
    });
});

// Email validation
function validateEmail(email) {
    const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return regex.test(email);
}

document.getElementById('orgEmail').addEventListener('blur', function() {
    const email = this.value;
    const emailHelp = document.getElementById('emailHelp');

    if (!validateEmail(email)) {
        emailHelp.textContent = "Please enter a valid email address.";
        emailHelp.classList.remove('text-muted');
        emailHelp.classList.add('text-danger');
        this.classList.add('is-invalid');
    } else {
        emailHelp.textContent = "Email address is valid.";
        emailHelp.classList.remove('text-danger');
        emailHelp.classList.add('text-success');
        this.classList.remove('is-invalid');
    }
});

// Phone validation
function validatePhone(phone) {
    const regex = /^\d{10}$/;
    return regex.test(phone);
}

document.getElementById('orgPhone').addEventListener('blur', function() {
    const phone = this.value;
    const phoneHelp = document.getElementById('phoneHelp');

    if (!validatePhone(phone)) {
        phoneHelp.textContent = "Please enter a 10-digit phone number.";
        phoneHelp.classList.remove('text-muted');
        phoneHelp.classList.add('text-danger');
        this.classList.add('is-invalid');
    } else {
        phoneHelp.textContent = "Phone number is valid.";
        phoneHelp.classList.remove('text-danger');
        phoneHelp.classList.add('text-success');
        this.classList.remove('is-invalid');
    }
});

// Save organization details and move to the next page
document.getElementById('nextBtn').addEventListener('click', function() {
    if (!checkFormCompletion()) {
        alert("Please fill in all required fields before proceeding.");
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
</script>

<?php include 'footer.php'; ?>