<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Organization Information</h3>
            <p>Please provide the details of your organization.</p>

            <!-- Organization Information Form s-->
            <form id="orgInfoForm">
                <div class="form-group">
                    <label for="orgName">Organization Name:</label>
                    <input type="text" class="form-control" id="orgName" required>
                </div>
                <div class="form-group">
                    <label for="orgPhone">Organization Phone Number:</label>
                    <input type="tel" class="form-control" id="orgPhone" required>
                </div>
                <div class="form-group">
                    <label for="orgEmail">Organization Email:</label>
                    <input type="email" class="form-control" id="orgEmail" required>
                </div>
                <div class="form-group">
                    <label for="orgKraPin">Organization KRA PIN:</label>
                    <input type="text" class="form-control" id="orgKraPin" required>
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary mt-4">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right mt-4">Next</button>
            </form>
        </div>
    </div>
</div>
<script>
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
    return allFilled; // Ensures validation properly returns a boolean
}

// Attach input event listeners for validation
requiredFields.forEach(field => {
    field.addEventListener('input', checkFormCompletion);
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

    window.location.href = 'data_request.php'; // Redirect to the next page
});

// Back button navigation
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'personal_information.php'; // Redirect back to the previous step
});

// Load data on page load
document.addEventListener('DOMContentLoaded', loadOrgDetails);
</script>

<?php include 'footer.php'; ?>
