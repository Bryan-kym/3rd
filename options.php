<?php
ob_start();
require_once 'auth.php';
include 'header.php';

try {
    $userId = authenticate();
} catch (Exception $e) {
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
?>

<div class="category-selection-container">
    <div class="category-card-wrapper">
        <div class="category-header">
            <h2 class="category-title text-primary">Select Your Category</h2>
            <p class="category-subtitle">Please choose the category that best describes you</p>
        </div>
        
        <form id="step2Form">
            <div class="category-grid">
                <!-- Taxpayer -->
                <div class="category-option">
                    <input class="category-radio" type="radio" name="category" id="taxpayer" value="taxpayer" required>
                    <label class="category-label" for="taxpayer">
                        <div class="category-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h3 class="category-name">Taxpayer</h3>
                        <p class="category-description">Individual taxpayer</p>
                    </label>
                </div>
                
                <!-- Tax Agent -->
                <div class="category-option">
                    <input class="category-radio" type="radio" name="category" id="taxagent" value="taxagent" required>
                    <label class="category-label" for="taxagent">
                        <div class="category-icon">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                        <h3 class="category-name">Tax Agent</h3>
                        <p class="category-description">Certified tax professional or accountant</p>
                    </label>
                </div>
                
                <!-- Student -->
                <div class="category-option">
                    <input class="category-radio" type="radio" name="category" id="student" value="student" required>
                    <label class="category-label" for="student">
                        <div class="category-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h3 class="category-name">Student</h3>
                        <p class="category-description">Currently enrolled in an educational institution</p>
                    </label>
                </div>
                
                <!-- Researcher -->
                <div class="category-option">
                    <input class="category-radio" type="radio" name="category" id="researcher" value="researcher" required>
                    <label class="category-label" for="researcher">
                        <div class="category-icon">
                            <i class="bi bi-flask"></i>
                        </div>
                        <h3 class="category-name">Researcher</h3>
                        <p class="category-description">Academic or professional researcher</p>
                    </label>
                </div>
                
                <!-- Private Company -->
                <div class="category-option">
                    <input class="category-radio" type="radio" name="category" id="privatecompany" value="privatecompany" required>
                    <label class="category-label" for="privatecompany">
                        <div class="category-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <h3 class="category-name">Private Company</h3>
                        <p class="category-description">Privately held business entity</p>
                    </label>
                </div>
                
                <!-- Public Company -->
                <div class="category-option">
                    <input class="category-radio" type="radio" name="category" id="publiccompany" value="publiccompany" required>
                    <label class="category-label" for="publiccompany">
                        <div class="category-icon">
                            <i class="bi bi-bank2"></i>
                        </div>
                        <h3 class="category-name">Public Company</h3>
                        <p class="category-description">Publicly traded corporation</p>
                    </label>
                </div>
            </div>

            <!-- Conditional Text Input for "Others" -->
            <div class="other-description" id="otherDescription" style="display: none;">
                <label for="description" class="description-label">Please describe your category</label>
                <textarea class="description-input" id="description" rows="2" maxlength="200" placeholder="Briefly describe your situation (max 200 characters)"></textarea>
                <div class="character-count"><span id="charCount">0</span>/200</div>
            </div>

            <!-- Navigation Buttons -->
            <div class="navigation-buttons">
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
        --border-radius: 0.5rem;
        --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .category-selection-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 120px);
        padding: 2rem;
        background-color: var(--light-grey);
    }

    .category-card-wrapper {
        background: var(--light-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 1200px;
        padding: 2.5rem;
        margin: 0 auto;
    }

    .category-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .category-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .category-subtitle {
        color: var(--grey-color);
        font-size: 1rem;
        margin-bottom: 0;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .category-option {
        position: relative;
    }

    .category-radio {
        position: absolute;
        opacity: 0;
    }

    .category-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem 1.5rem;
        border: 1px solid #e0e0e0;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        height: 100%;
        background: var(--light-color);
    }

    .category-label:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border-color: var(--primary-color);
    }

    .category-radio:checked + .category-label {
        border-color: var(--primary-color);
        background-color: var(--primary-light);
        box-shadow: 0 5px 15px rgba(217, 35, 46, 0.1);
    }

    .category-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-bottom: 1.25rem;
        color: var(--primary-color);
        background-color: var(--primary-light);
        transition: var(--transition);
    }

    .category-radio:checked + .category-label .category-icon {
        background-color: var(--primary-color);
        color: var(--light-color);
    }

    .category-name {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--secondary-color);
        text-align: center;
    }

    .category-description {
        font-size: 0.9rem;
        color: var(--grey-color);
        text-align: center;
        margin-bottom: 0;
    }

    .other-description {
        margin-top: 2rem;
        padding: 1.5rem;
        background-color: var(--light-grey);
        border-radius: var(--border-radius);
    }

    .description-label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 500;
        color: var(--secondary-color);
    }

    .description-input {
        width: 100%;
        padding: 1rem;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        resize: none;
        transition: var(--transition);
    }

    .description-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(217, 35, 46, 0.15);
    }

    .character-count {
        text-align: right;
        font-size: 0.8rem;
        color: var(--grey-color);
        margin-top: 0.5rem;
    }

    .navigation-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 3rem;
    }

    /* Button Styles - Consistent with Dashboard */
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
        .category-card-wrapper {
            padding: 1.5rem;
        }
        
        .category-grid {
            grid-template-columns: 1fr;
        }
        
        .navigation-buttons {
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