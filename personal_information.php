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
            <h3 class="card-title">Personal Information</h3>
            <p>Please fill in your personal information below.</p>

            <!-- Personal Information Form -->
            <form id="step3Form">
                <div class="form-group" id="kraPinField">
                    <label for="kra_pin">KRA PIN</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="kra_pin" name="kra_pin">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" id="verifyKraPinBtn">Verify</button>
                        </div>
                    </div>
                    <p id="kraPinError" class="text-danger"></p>
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

// Check if coming from proper flow by verifying required localStorage items
window.addEventListener('load', async function() {
    // Check for auth token and required flow items
    if (!localStorage.getItem('authToken') || 
        !localStorage.getItem('nda_form') || 
        !localStorage.getItem('selectedCategory')) {
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
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Token validation error:', error);
        window.location.href = 'login.html';
    }

    // Load any previously saved form data
    loadFormData();
});

// Select required input fields and the 'Next' button
const requiredFields = document.querySelectorAll('#step3Form input[required]');
const nextBtn = document.getElementById('nextBtn');

// Function to check if all required fields are filled
function checkFormCompletion() {
    const allFilled = Array.from(requiredFields).every(field => field.value.trim() !== '');
    nextBtn.disabled = !allFilled;
}

// Function to save form data to localStorage
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

// Function to load saved form data
function loadFormData() {
    const savedData = localStorage.getItem('personalInfo');
    if (savedData) {
        const formData = JSON.parse(savedData);
        document.getElementById('surname').value = formData.surname || '';
        document.getElementById('othernames').value = formData.othernames || '';
        document.getElementById('email').value = formData.email || '';
        document.getElementById('phone').value = formData.phone || '';
        document.getElementById('kra_pin').value = formData.kra_pin || '';

        // If this is a student, skip KRA PIN verification
        const category = localStorage.getItem('selectedCategory');
        if (category === 'student') {
            document.getElementById('kraPinField').style.display = 'none';
            document.getElementById('otherFields').style.display = 'block';
            const otherFields = document.querySelectorAll('#otherFields input');
            otherFields.forEach(field => field.disabled = false);
            checkFormCompletion();
        }
    }
}

function validateEmail(email) {
    const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return regex.test(email);
}

document.getElementById('email').addEventListener('blur', function() {
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

function validatePhone(phone) {
    const regex = /^\d{10}$/;
    return regex.test(phone);
}

document.getElementById('phone').addEventListener('blur', function() {
    const phone = this.value;
    const phoneHelp = document.getElementById('phoneHelp');

    if (!validatePhone(phone)) {
        phoneHelp.textContent = "Please enter a valid 10-digit phone number.";
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

// Enable 'Next' button dynamically when typing
requiredFields.forEach(field => {
    field.addEventListener('input', checkFormCompletion);
});

// Save form data and send OTP when 'Next' is clicked
document.getElementById('nextBtn').addEventListener('click', function() {
    // Validate email and phone number
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const emailHelp = document.getElementById('emailHelp');
    const phoneHelp = document.getElementById('phoneHelp');

    let isValid = true;

    if (!validateEmail(email)) {
        emailHelp.textContent = "Please enter a valid email address.";
        emailHelp.classList.remove('text-muted');
        emailHelp.classList.add('text-danger');
        document.getElementById('email').classList.add('is-invalid');
        isValid = false;
    } else {
        emailHelp.textContent = "Email address is valid.";
        emailHelp.classList.remove('text-danger');
        emailHelp.classList.add('text-success');
        document.getElementById('email').classList.remove('is-invalid');
    }

    if (!validatePhone(phone)) {
        phoneHelp.textContent = "Please enter a valid 10-digit phone number.";
        phoneHelp.classList.remove('text-muted');
        phoneHelp.classList.add('text-danger');
        document.getElementById('phone').classList.add('is-invalid');
        isValid = false;
    } else {
        phoneHelp.textContent = "Phone number is valid.";
        phoneHelp.classList.remove('text-danger');
        phoneHelp.classList.add('text-success');
        document.getElementById('phone').classList.remove('is-invalid');
    }

    if (!isValid) return;

    saveFormData();

    fetch('send_otp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
        },
        body: 'email=' + encodeURIComponent(email)
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
            $('#otpModal').modal('show');
        } else {
            alert("Error sending OTP. Try again.");
        }
    })
    .catch(error => console.error('Error:', error));
});

// OTP Verification
document.getElementById('verifyOtpBtn').addEventListener('click', function() {
    let email = document.getElementById('email').value;
    let otp = document.getElementById('otpInput').value;

    fetch('verify_otp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
        },
        body: 'email=' + encodeURIComponent(email) + '&otp=' + encodeURIComponent(otp)
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
            $('#otpModal').modal('hide');
            const category = localStorage.getItem('selectedCategory');
            if (category === 'student' || category === 'researcher') {
                window.location.href = 'institution_details.php';
            } else if (category === 'privatecompany' || category === 'publiccompany') {
                window.location.href = 'org.php';
            } else {
                window.location.href = 'data_request.php';
            }
        } else {
            document.getElementById('otpError').innerText = data.message;
        }
    })
    .catch(error => console.error('Error:', error));
});

// Resend OTP
document.getElementById('resendOtpBtn').addEventListener('click', function() {
    let email = document.getElementById('email').value;

    fetch('send_otp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
        },
        body: 'email=' + encodeURIComponent(email)
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
            alert("A new OTP has been sent.");
        } else {
            alert("Error resending OTP. Try again.");
        }
    })
    .catch(error => console.error('Error:', error));
});

// Handle 'Back' button click
document.getElementById('backBtn').addEventListener('click', function() {
    saveFormData();
    window.location.href = 'options.php';
});

document.getElementById('verifyKraPinBtn').addEventListener('click', function() {
    const kraPin = document.getElementById('kra_pin').value;
    const kraPinError = document.getElementById('kraPinError');

    if (!kraPin) {
        kraPinError.innerText = "Please enter a KRA PIN.";
        return;
    }

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
</script>

<?php include 'footer.php'; ?>