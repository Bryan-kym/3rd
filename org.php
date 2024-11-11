<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Organization Information</h3>
            <p>Please provide the details of your organization.</p>

            <!-- Organization Information Form -->
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
// Handle navigation to the previous step
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'personal_information.php'; // Redirect back to the previous step
});

// Handle navigation to the next step
document.getElementById('nextBtn').addEventListener('click', function() {
    // Gather form data
    const orgName = document.getElementById('orgName').value;
    const orgPhone = document.getElementById('orgPhone').value;
    const orgEmail = document.getElementById('orgEmail').value;
    const orgKraPin = document.getElementById('orgKraPin').value;

    // Store data in localStorage or handle your database submission here
    localStorage.setItem('orgName', orgName);
    localStorage.setItem('orgPhone', orgPhone);
    localStorage.setItem('orgEmail', orgEmail);
    localStorage.setItem('orgKraPin', orgKraPin);

    // Redirect to the next step (modify this to the appropriate next step page)
    window.location.href = 'data_request.php'; // Update with the actual next step
});
</script>

<?php include 'footer.php'; ?>
