<?php

ob_start(); // Start output buffering
require_once 'auth.php'; // Include your authentication functions
include 'header.php';


// Check if user is authenticated
try {
    $userId = authenticate(); // This will redirect if not authenticated

    // Check if coming from request.php by checking for nda_form in localStorage
    // We'll verify this in the JavaScript since PHP can't directly check localStorage
} catch (Exception $e) {
    // Redirect to login if not authenticated
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Get token from session or headers
$token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h3 class="card-title text-center text-primary fw-bold">Select Your Category</h3>
                    <p class="text-muted text-center mb-0">Please choose the category that best describes you</p>
                </div>
                
                <div class="card-body px-4 px-md-5 py-4">
                    <!-- Category Selection Form -->
                    <form id="step2Form">
                        <div class="row g-3">
                            <!-- Radio Buttons Styled as Cards -->
                            <div class="col-md-6">
                                <input class="btn-check" type="radio" name="category" id="taxpayer" value="taxpayer" required>
                                <label class="category-card" for="taxpayer">
                                    <div class="category-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <h5 class="category-title">Taxpayer</h5>
                                    <p class="category-desc">Individual taxpayer</p>
                                </label>
                            </div>
                            
                            <div class="col-md-6">
                                <input class="btn-check" type="radio" name="category" id="taxagent" value="taxagent" required>
                                <label class="category-card" for="taxagent">
                                    <div class="category-icon bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <h5 class="category-title">Tax Agent</h5>
                                    <p class="category-desc">Certified tax professional or accountant</p>
                                </label>
                            </div>
                            
                            <div class="col-md-6">
                                <input class="btn-check" type="radio" name="category" id="student" value="student" required>
                                <label class="category-card" for="student">
                                    <div class="category-icon bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <h5 class="category-title">Student</h5>
                                    <p class="category-desc">Currently enrolled in an educational institution</p>
                                </label>
                            </div>
                            
                            <div class="col-md-6">
                                <input class="btn-check" type="radio" name="category" id="researcher" value="researcher" required>
                                <label class="category-card" for="researcher">
                                    <div class="category-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <h5 class="category-title">Researcher</h5>
                                    <p class="category-desc">Academic or professional researcher</p>
                                </label>
                            </div>
                            
                            <div class="col-md-6">
                                <input class="btn-check" type="radio" name="category" id="privatecompany" value="privatecompany" required>
                                <label class="category-card" for="privatecompany">
                                    <div class="category-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h5 class="category-title">Private Company</h5>
                                    <p class="category-desc">Privately held business entity</p>
                                </label>
                            </div>
                            
                            <div class="col-md-6">
                                <input class="btn-check" type="radio" name="category" id="publiccompany" value="publiccompany" required>
                                <label class="category-card" for="publiccompany">
                                    <div class="category-icon bg-purple bg-opacity-10 text-purple">
                                        <i class="fas fa-landmark"></i>
                                    </div>
                                    <h5 class="category-title">Public Company</h5>
                                    <p class="category-desc">Publicly traded corporation</p>
                                </label>
                            </div>
                            
                            <!-- <div class="col-12">
                                <input class="btn-check" type="radio" name="category" id="other" value="other">
                                <label class="category-card" for="other">
                                    <div class="category-icon bg-secondary bg-opacity-10 text-secondary">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </div>
                                    <h5 class="category-title">Other</h5>
                                    <p class="category-desc">None of the above categories fit</p>
                                </label>
                            </div> -->
                        </div>

                        <!-- Conditional Text Input for "Others" -->
                        <div class="form-group mt-4" id="otherDescription" style="display: none;">
                            <label for="description" class="form-label">Please describe your category</label>
                            <textarea class="form-control" id="description" rows="2" maxlength="200" placeholder="Briefly describe your situation (max 200 characters)"></textarea>
                            <div class="form-text text-end"><span id="charCount">0</span>/200</div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-5 pt-2">
                            <button type="button" id="backBtn" class="btn btn-outline-secondary px-4 py-2">
                                <i class="fas fa-arrow-left me-2"></i> Back
                            </button>
                            <button type="button" id="nextBtn" class="btn btn-primary px-4 py-2" disabled>
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
    :root {
        --purple: #6f42c1;
    }
    
    .bg-purple {
        background-color: var(--purple);
    }
    
    .text-purple {
        color: var(--purple);
    }
    
    .category-card {
        display: block;
        padding: 1.5rem;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
        text-align: center;
    }
    
    .category-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }
    
    .btn-check:checked + .category-card {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .category-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .category-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }
    
    .category-desc {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0;
    }
</style>

<script>
     // Add Font Awesome (if not already loaded)
     document.addEventListener('DOMContentLoaded', function() {
        // Other option toggle
        const otherRadio = document.getElementById('other');
        const otherDescription = document.getElementById('otherDescription');
        
        document.querySelectorAll('input[name="category"]').forEach(radio => {
            radio.addEventListener('change', function() {
                otherDescription.style.display = this.id === 'other' ? 'block' : 'none';
                document.getElementById('nextBtn').disabled = !this.checked;
            });
        });
        
        // Character count for description
        const description = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        if (description) {
            description.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
        }
    });
    // Store token in localStorage if it came from session
    const token = '<?php echo $token; ?>';
    if (token && !localStorage.getItem('authToken')) {
        localStorage.setItem('authToken', token);
    }

    // Check if coming from request.php by verifying nda_form exists in localStorage
    window.addEventListener('load', async function() {
        if (!localStorage.getItem('authToken') || !localStorage.getItem('nda_form')) {
            // If no token or not coming from request.php, redirect to dashboard
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
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Token validation error:', error);
            window.location.href = 'login.html';
        }
    });

    // Enable 'Next' button based on category selection and description input
    document.querySelectorAll('input[name="category"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const nextBtn = document.getElementById('nextBtn');
            const otherDescription = document.getElementById('otherDescription');

            // Store selected category in localStorage
            localStorage.setItem('selectedCategory', this.value);

            if (this.value === 'others') {
                otherDescription.style.display = 'block';
                checkDescription(); // Check if description is valid
            } else {
                otherDescription.style.display = 'none';
                document.getElementById('description').value = ''; // Clear description field if hidden
                nextBtn.disabled = false; // Enable Next button for other categories
            }
        });
    });

    // Check if description is valid
    document.getElementById('description').addEventListener('input', checkDescription);

    function checkDescription() {
        const nextBtn = document.getElementById('nextBtn');
        const description = document.getElementById('description').value;

        // Enable Next button only if description is provided
        nextBtn.disabled = description.trim() === '' || description.length > 200;
    }

    // Handle navigation to the next or previous steps
    document.getElementById('nextBtn').addEventListener('click', async function() {
        // Save the description in localStorage if the category is "Others"
        const selectedCategory = localStorage.getItem('selectedCategory');
        if (selectedCategory === 'others') {
            const description = document.getElementById('description').value;
            localStorage.setItem('description', description); // Save description if "Others" is selected
        } else {
            localStorage.removeItem('description'); // Clear description if not "Others"
        }

        // redirect to the next step
        if (selectedCategory === 'taxagent') {
            window.location.href = 'taxagent.php';
        } else {
            window.location.href = 'personal_information.php';
        }
    });

    document.getElementById('backBtn').addEventListener('click', function() {
        window.location.href = 'request.php'; // Redirect back to Step 1
    });
</script>

<?php include 'footer.php';
ob_end_flush();
?>