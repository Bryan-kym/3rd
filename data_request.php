<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Data Request</h3>
            <p>Please fill in the details of your data request below.</p>

            <!-- Data Request Form form -->
            <form id="dataRequestForm" method="POST" action="your_submission_handler.php" enctype="multipart/form-data">
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
                </div>
                <input type="text" class="form-control mb-3" id="specificFields" name="specificFields" placeholder="Selected fields will appear here">

                <!-- File Upload for Template (Optional) -->
                <div class="form-group">
                    <label for="dataTemplate">Upload Specific Template (Optional)</label>
                    <input type="file" class="form-control" id="dataTemplate" name="dataTemplate" accept=".pdf,.doc,.docx,.xls,.xlsx">
                </div>

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
// Function to load saved data into form fields
function loadDataRequestInfo() {
    let dataRequestInfo = JSON.parse(localStorage.getItem('dataRequestInfo')) || {};

    document.getElementById('dataDescription').value = dataRequestInfo.dataDescription || '';
    document.getElementById('specificFields').value = dataRequestInfo.specificFields || '';
    document.getElementById('dateFrom').value = dataRequestInfo.dateFrom || '';
    document.getElementById('dateTo').value = dataRequestInfo.dateTo || '';
    document.getElementById('requestReason').value = dataRequestInfo.requestReason || '';
}

// Prevent duplicate field selections
document.querySelectorAll('.field-option').forEach(option => {
    option.addEventListener('click', function() {
        const fieldInput = document.getElementById('specificFields');
        let dataRequestInfo = JSON.parse(localStorage.getItem('dataRequestInfo')) || {};
        let selectedFields = fieldInput.value ? fieldInput.value.split(', ') : [];
        const newValue = this.getAttribute('data-value');

        if (!selectedFields.includes(newValue)) {
            selectedFields.push(newValue);
            fieldInput.value = selectedFields.join(', ');
            dataRequestInfo.specificFields = fieldInput.value;
            localStorage.setItem('dataRequestInfo', JSON.stringify(dataRequestInfo));
        }
    });
});

// Validate date fields
function validateDates() {
    let dateFrom = document.getElementById('dateFrom').value;
    let dateTo = document.getElementById('dateTo').value;
    let today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format

    if (dateFrom && dateTo) {
        if (dateTo < dateFrom) {
            alert("❌ 'Date To' cannot be earlier than 'Date From'.");
            document.getElementById('dateTo').value = ""; // Reset invalid input
            return false;
        }
        if (dateFrom > today || dateTo > today) {
            alert("❌ Dates cannot be in the future.");
            if (dateFrom > today) document.getElementById('dateFrom').value = "";
            if (dateTo > today) document.getElementById('dateTo').value = "";
            return false;
        }
    }
    return true;
}

// Event listeners for date validation
document.getElementById('dateFrom').addEventListener('change', validateDates);
document.getElementById('dateTo').addEventListener('change', validateDates);

// Validate all required fields
function validateForm() {
    let dataDescription = document.getElementById('dataDescription').value.trim();
    let specificFields = document.getElementById('specificFields').value.trim();
    let dateFrom = document.getElementById('dateFrom').value.trim();
    let dateTo = document.getElementById('dateTo').value.trim();
    let requestReason = document.getElementById('requestReason').value.trim();

    if (!dataDescription || !specificFields || !dateFrom || !dateTo || !requestReason) {
        alert("❌ Please fill in all required fields before proceeding.");
        return false;
    }
    return true;
}

// Save data and go to the next page
document.getElementById('nextBtn').addEventListener('click', function() {
    if (validateForm() && validateDates()) {
        let dataRequestInfo = {
            dataDescription: document.getElementById('dataDescription').value,
            specificFields: document.getElementById('specificFields').value,
            dateFrom: document.getElementById('dateFrom').value,
            dateTo: document.getElementById('dateTo').value,
            requestReason: document.getElementById('requestReason').value
        };

        // Handle file upload if a file is selected
        let fileInput = document.getElementById('dataTemplate');
        if (fileInput && fileInput.files.length > 0) {
            let fileName = fileInput.files[0].name;
            let userName = localStorage.getItem('userName') || 'user';
            let newFileName = userName + '_template_' + fileName;
            let filePath = 'uploads/templates/' + newFileName;
            dataRequestInfo.templatePath = filePath;
        }

        localStorage.setItem('dataRequestInfo', JSON.stringify(dataRequestInfo));
        window.location.href = 'attachments.php';
    }
});

// Function to determine the previous page based on the selected category
document.getElementById('backBtn').addEventListener('click', function() {
    let category = localStorage.getItem('selectedCategory');
    let previousPage = 'personal_information.php';

    switch (category) {
        case 'taxagent':
            previousPage = 'client.php';
            break;
        case 'taxpayer':
            previousPage = 'personal_information.php';
            break;
        case 'student':
        case 'researcher':
            previousPage = 'institution_details.php';
            break;
        case 'public_company':
        case 'private_company':
            previousPage = 'org.php';
            break;
    }

    window.location.href = previousPage;
});

// Load saved data on page load
document.addEventListener('DOMContentLoaded', loadDataRequestInfo);
</script>


<?php include 'footer.php'; ?>
