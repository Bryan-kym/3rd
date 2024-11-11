<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Data Request</h3>
            <p>Please fill in the details of your data request below.</p>

            <!-- Data Request Form -->
            <form id="dataRequestForm" method="POST" action="your_submission_handler.php">
                <!-- Hidden input for category -->
                <input type="hidden" name="category" id="category" value="">

                <div class="form-group">
                    <label for="dataDescription">Description of the Data Requested</label>
                    <input type="text" class="form-control" id="dataDescription" name="dataDescription" required>
                </div>

                <!-- Options for specific fields -->
                <label for="specificFields">Select Specific Fields:</label>
                <div id="fieldOptions" class="mb-3">
                    <span class="badge bg-secondary field-option" data-value= "KRA pin">Pin Number</span>
                    <span class="badge bg-secondary field-option" data-value="Taxpayer Name">Taxpayer Name</span>
                    <span class="badge bg-secondary field-option" data-value="Station">Station</span>
                    <span class="badge bg-secondary field-option" data-value="Amount">Amount</span>
                    <!-- <span class="badge bg-secondary field-option" data-value="Field 5">Field 5</span> -->
                </div>
                <input type="text" class="form-control mb-3" id="specificFields" name="specificFields" placeholder="Selected fields will appear here">

                <!-- Custom fields input -->
                <!-- <div class="form-group">
                    <label for="customField">Add Other Fields (comma-separated)</label>
                    <input type="text" class="form-control" id="customField" placeholder="Type additional fields here">
                </div> -->

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dateFrom">Date From</label>
                            <input type="date" class="form-control" id="dateFrom" name="dateFrom" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dateTo">Date To</label>
                            <input type="date" class="form-control" id="dateTo" name="dateTo" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="requestReason">Reason for Requesting Data</label>
                    <textarea class="form-control" id="requestReason" name="requestReason" rows="3" required></textarea>
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right">Next</button>
            </form>
        </div>
    </div>
</div>

<script>
// Automatically fill the input box when selecting a field option
document.querySelectorAll('.field-option').forEach(option => {
    option.addEventListener('click', function() {
        const fieldInput = document.getElementById('specificFields');
        let currentValue = fieldInput.value;
        const newValue = this.getAttribute('data-value');

        if (currentValue) {
            currentValue += ', ' + newValue; // Append if there's already a value
        } else {
            currentValue = newValue; // Set the new value if empty
        }

        fieldInput.value = currentValue;
    });
});


// Set the category value based on user selection
function setCategory(category) {
    document.getElementById('category').value = category;
}

// Navigation to previous step based on user category
document.getElementById('backBtn').addEventListener('click', function() {
    const category = localStorage.getItem('selectedCategory');

    if (category === 'student' || category === 'researcher') {
        window.location.href = 'institution_details.php'; // Redirect to institution details page
    } else if (category === 'privatecompany' || category === 'publiccompany') {
        window.location.href = 'org.php'; // Redirect to organization page
    } else if (category === 'taxagent') {
        window.location.href = 'client.php'; // Redirect to client page
    } else {
        window.location.href = 'personal_information.php'; // Redirect back to personal information page
    }
});

// Navigate to attachments page
document.getElementById('nextBtn').addEventListener('click', function() {
    const category = localStorage.getItem('selectedCategory');

    // Store data request details in localStorage (if needed for future use)
    localStorage.setItem('dataDescription', document.getElementById('dataDescription').value);
    localStorage.setItem('specificFields', document.getElementById('specificFields').value);
    localStorage.setItem('dateFrom', document.getElementById('dateFrom').value);
    localStorage.setItem('dateTo', document.getElementById('dateTo').value);
    localStorage.setItem('requestReason', document.getElementById('requestReason').value);

    // Set the category value before redirection
    setCategory(category); // Call this function before redirecting

    // Redirect to the attachments page
    window.location.href = 'attachments.php'; // Redirect to attachments page
});
</script>

<?php include 'footer.php'; ?>
