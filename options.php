<?php
ob_start();
require_once 'auth.php';
include 'header.php';

try {
    $userId = authenticate(); // This will redirect if not authenticated
    
    // Check if coming from request.php by checking for nda_form in localStorage
    // We'll verify this in the JavaScript since PHP can't directly check localStorage
} catch (Exception $e) {
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Get token from session or headers
$token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : 
         (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
?>

<div class="container mt-5 w-50">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Select Your Category</h3>
            <p>Please choose your category from the options below.</p>

            <!-- Category Selection Form -->
            <form id="step2Form">
                <div class="row">
                    <!-- Radio Buttons Styled as Buttons -->
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="taxpayer" value="taxpayer" required>
                        <label class="btn btn-outline-primary w-100" for="taxpayer">Taxpayer</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="taxagent" value="taxagent" required>
                        <label class="btn btn-outline-primary w-100" for="taxagent">Tax Agent</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="student" value="student" required>
                        <label class="btn btn-outline-primary w-100" for="student">Student</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="researcher" value="researcher" required>
                        <label class="btn btn-outline-primary w-100" for="researcher">Researcher</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="privatecompany" value="privatecompany" required>
                        <label class="btn btn-outline-primary w-100" for="privatecompany">Private Company</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="publiccompany" value="publiccompany" required>
                        <label class="btn btn-outline-primary w-100" for="publiccompany">Public Company</label>
                    </div>
                </div>

                <!-- Conditional Text Input for "Others" -->
                <div class="form-group mt-3" id="otherDescription" style="display: none;">
                    <label for="description">Please provide a brief description (max 200 characters):</label>
                    <input type="text" class="form-control" id="description" maxlength="200">
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary mt-4">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right mt-4" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

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

<?php include 'footer.php'; ?>