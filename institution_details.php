<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Institution Details</h3>
            <p>Please fill in your institution's details below.</p>

            <!-- Institution Details Form ff -->
            <form id="institutionForm">
                <div class="form-group">
                    <label for="inst_name">Institution Name</label>
                    <input type="text" class="form-control" id="inst_name" name="inst_name" required>
                </div>

                <div class="form-group">
                    <label for="inst_email">Institution Email</label>
                    <input type="email" class="form-control" id="inst_email" name="inst_email" required>
                    <small id="emailHelp" class="form-text text-muted">Please enter a valid email address.</small>
                </div>

                <div class="form-group">
                    <label for="inst_phone">Institution Phone Number</label>
                    <input type="tel" class="form-control" id="inst_phone" name="inst_phone" required>
                    <small id="phoneHelp" class="form-text text-muted">Please enter a 10-digit phone number.</small>
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

<script>
// Function to load saved institution details
function loadInstitutionDetails() {
    let insDetails = JSON.parse(localStorage.getItem('ins_details')) || {};

    document.getElementById('inst_name').value = insDetails.inst_name || '';
    document.getElementById('inst_email').value = insDetails.inst_email || '';
    document.getElementById('inst_phone').value = insDetails.inst_phone || '';

    checkFormCompletion(); // Ensure button state is updated after loading
}

// Enable 'Next' button when all required fields are filled
const requiredFields = document.querySelectorAll('#institutionForm input[required]');
const nextBtn = document.getElementById('nextBtn');

function checkFormCompletion() {
    let allFilled = Array.from(requiredFields).every(field => field.value.trim() !== '');
    nextBtn.disabled = !allFilled;
    return allFilled; // Ensures validation properly returns a boolean
}

// Attach input event listeners for validation
requiredFields.forEach(field => {
    field.addEventListener('input', checkFormCompletion);
});

function validateEmail(email) {
        const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return regex.test(email);
    }

    document.getElementById('inst_email').addEventListener('blur', function() {
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
        // Check if the phone number is exactly 10 digits and contains only numbers
        const regex = /^\d{10}$/;
        return regex.test(phone);
    }

    document.getElementById('inst_phone').addEventListener('blur', function() {
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

// Save institution details and move to the next page
document.getElementById('nextBtn').addEventListener('click', function() {
    if (!checkFormCompletion()) {
        alert("Please fill in all required fields before proceeding.");
        return;
    }

    let insDetails = {
        inst_name: document.getElementById('inst_name').value.trim(),
        inst_email: document.getElementById('inst_email').value.trim(),
        inst_phone: document.getElementById('inst_phone').value.trim()
    };

    localStorage.setItem('ins_details', JSON.stringify(insDetails));

    window.location.href = 'data_request.php'; // Redirect to data request page
});

// Back button navigation
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'personal_information.php'; // Redirect back to personal information page
});

// Load data on page load
document.addEventListener('DOMContentLoaded', loadInstitutionDetails);
</script>


<?php include 'footer.php'; ?>
