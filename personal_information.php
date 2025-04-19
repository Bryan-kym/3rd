<?php
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
                
                <div class="card-body px-5 pt-4 pb-5">
                    <form id="step3Form" class="needs-validation" novalidate>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="surname" name="surname" 
                                           placeholder=" " required>
                                    <label for="surname" class="form-label">Surname</label>
                                    <div class="invalid-feedback">Please enter your surname</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="othernames" name="othernames" 
                                           placeholder=" " required>
                                    <label for="othernames" class="form-label">Other Names</label>
                                    <div class="invalid-feedback">Please enter your other names</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder=" " required readonly>
                            <label for="email" class="form-label">Email Address</label>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder=" " required>
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <div class="invalid-feedback">Please enter a valid phone number</div>
                                    <small class="text-muted mt-1 d-block">Include country code (e.g. +254712345678)</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="kra_pin" name="kra_pin" 
                                           placeholder=" " required>
                                    <label for="kra_pin" class="form-label">KRA PIN</label>
                                    <div class="invalid-feedback">Please enter your KRA PIN</div>
                                    <small class="text-muted mt-1 d-block">Format: A123456789X</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-5">
                            <button type="button" id="backBtn" class="btn btn-outline-secondary px-4 py-2">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <button type="button" id="nextBtn" class="btn btn-primary px-4 py-2">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
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
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    
    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
    }
</style>

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