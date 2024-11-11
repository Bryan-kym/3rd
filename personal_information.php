<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Personal Information</h3>
            <p>Please fill in your personal information below.</p>

            <!-- Personal Information Form -->
            <form id="step3Form">
                <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" class="form-control" id="surname" name="surname" required>
                </div>
                
                <div class="form-group">
                    <label for="othernames">Other Names</label>
                    <input type="text" class="form-control" id="othernames" name="othernames" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>

                <!-- KRA PIN (Hidden for Students) -->
                <div class="form-group" id="kraPinField">
                    <label for="kra_pin">KRA PIN</label>
                    <input type="text" class="form-control" id="kra_pin" name="kra_pin">
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

<script>
// Enable 'Next' button when required fields are filled
const requiredFields = document.querySelectorAll('#step3Form input[required]');
const nextBtn = document.getElementById('nextBtn');

requiredFields.forEach(field => {
    field.addEventListener('input', checkFormCompletion);
});

function checkFormCompletion() {
    nextBtn.disabled = !Array.from(requiredFields).every(field => field.value.trim() !== '');
}

// Hide KRA PIN if user category is "Student"
document.addEventListener('DOMContentLoaded', function() {
    const category = localStorage.getItem('selectedCategory');
    if (category === 'student') {
        document.getElementById('kraPinField').style.display = 'none';
    } else {
        document.getElementById('kraPinField').style.display = 'block'; // Show KRA PIN for other categories
    }
});

// Navigation to next or previous steps
document.getElementById('nextBtn').addEventListener('click', function() {
    const category = localStorage.getItem('selectedCategory');

    // Store personal information in localStorage
    localStorage.setItem('surname', document.getElementById('surname').value);
    localStorage.setItem('othernames', document.getElementById('othernames').value);
    localStorage.setItem('email', document.getElementById('email').value);
    localStorage.setItem('phone', document.getElementById('phone').value);
    localStorage.setItem('kra_pin', document.getElementById('kra_pin').value);
    
    // Redirect based on selected category
    if (category === 'student' || category === 'researcher') {
        window.location.href = 'institution_details.php'; // Redirect to institution details page
    } else if (category === 'privatecompany' || category === 'publiccompany') {
        window.location.href = 'org.php'; // Redirect to organization info page
    } else {
        window.location.href = 'data_request.php'; // Redirect to data request page for others
    }
});

document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'options.php'; // Redirect back to Step 2
});
</script>

<?php include 'footer.php'; ?>
