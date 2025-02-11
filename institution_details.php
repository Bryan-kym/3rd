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
                </div>

                <div class="form-group">
                    <label for="inst_phone">Institution Phone Number</label>
                    <input type="tel" class="form-control" id="inst_phone" name="inst_phone" required>
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
const requiredFields = document.querySelectorAll('#institutionForm input[required]');
const nextBtn = document.getElementById('nextBtn');

requiredFields.forEach(field => {
    field.addEventListener('input', checkFormCompletion);
});

function checkFormCompletion() {
    nextBtn.disabled = !Array.from(requiredFields).every(field => field.value.trim() !== '');
}

// Navigation to next or previous steps
document.getElementById('nextBtn').addEventListener('click', function() {
    // Store institution information in localStorage
    localStorage.setItem('inst_name', document.getElementById('inst_name').value);
    localStorage.setItem('inst_email', document.getElementById('inst_email').value);
    localStorage.setItem('inst_phone', document.getElementById('inst_phone').value);
    
    // Redirect to Data Request Page
    window.location.href = 'data_request.php'; // Redirect to data request page
});

document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'personal_information.php'; // Redirect back to personal information page
});
</script>

<?php include 'footer.php'; ?>
