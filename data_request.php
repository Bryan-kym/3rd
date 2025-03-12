<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

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
                    <small id="descriptionHelp" class="form-text text-muted">0/50 characters</small>
                </div>

                <label for="specificFields">Select Specific Fields:</label>
                <div id="fieldOptions" class="mb-3">
                    <span class="badge bg-secondary field-option" data-value="KRA Pin">KRA Pin</span>
                    <span class="badge bg-secondary field-option" data-value="Taxpayer Name">Taxpayer Name</span>
                    <span class="badge bg-secondary field-option" data-value="Station">Station</span>
                    <span class="badge bg-secondary field-option" data-value="Amount">Amount</span>
                </div>
                <input type="text" class="form-control mb-3" id="specificFields" name="specificFields" placeholder="Selected fields will appear here">
                <small id="specificFieldsHelp" class="form-text text-muted">Maximum 150 characters allowed.</small>

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
                    <small id="charCount" class="form-text text-muted">0/250 characters</small>
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


    document.getElementById('specificFields').addEventListener('input', function() {
        const maxChar = 150;
        const input = this.value;
        const specificFieldsHelp = document.getElementById('specificFieldsHelp');
        const submitBtn = document.getElementById('submitBtn');

        if(input.length > maxChar){
            this.value = input.substring(0, maxChar);
            input = this.value;
        }

    });

    document.getElementById('dataDescription').addEventListener('input', function() {
        const maxChars = 50;
        let description = this.value;
        const descriptionHelp = document.getElementById('descriptionHelp');
        const submitBtn = document.getElementById('submitBtn');

        // Prevent input beyond 50 characters
        if (description.length > maxChars) {
            this.value = description.substring(0, maxChars); // Trim excess
            description = this.value; // Update variable after trimming
        }

        // Update character counter
        descriptionHelp.textContent = `${description.length}/${maxChars} characters`;

        // Change counter color based on character limit
        if (description.length >= maxChars) {
            descriptionHelp.classList.remove('text-muted', 'text-success');
            descriptionHelp.classList.add('text-danger');
        } else {
            descriptionHelp.classList.remove('text-danger');
            descriptionHelp.classList.add('text-muted');
        }

        // Enable/Disable submit button based on character count
        submitBtn.disabled = description.length === 0;
    });

    document.getElementById('requestReason').addEventListener('input', function() {
        const maxChars = 250;
        let requestReason = this.value;
        const charCount = document.getElementById('charCount');
        const submitBtn = document.getElementById('submitBtn');

        // Prevent input beyond 250 characters
        if (requestReason.length > maxChars) {
            this.value = requestReason.substring(0, maxChars); // Trim excess
            requestReason = this.value; // Update variable after trimming
        }

        // Update character counter
        charCount.textContent = `${requestReason.length}/${maxChars} characters`;

        // Change counter color based on character limit
        if (requestReason.length >= maxChars) {
            charCount.classList.remove('text-muted', 'text-success');
            charCount.classList.add('text-danger');
        } else {
            charCount.classList.remove('text-danger');
            charCount.classList.add('text-muted');
        }

        // Enable/Disable submit button based on character count
        submitBtn.disabled = requestReason.length === 0;
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
                let formData = new FormData();
                formData.append('file', fileInput.files[0]);

                // Generate a unique file name
                let userName = localStorage.getItem('userName') || 'user';
                let fileName = fileInput.files[0].name;
                let newFileName = userName + '_template_' + Date.now() + '_' + fileName;
                let fileType = newFileName.split('.').pop().toLowerCase();
                formData.append('fileName', newFileName);

                // Upload the file to the server
                fetch('upload_template.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text()) // First, get the response as text
                    .then(text => {
                        console.log("Raw response:", text); // Log the raw response
                        return JSON.parse(text); // Then try to parse it as JSON
                    })
                    .then(data => {
                        if (data.success) {
                            dataRequestInfo.templatePath = data.filePath;
                            dataRequestInfo.templateFileType = fileType;
                            localStorage.setItem('dataRequestInfo', JSON.stringify(dataRequestInfo));
                            window.location.href = 'attachments.php';
                        } else {
                            alert('File upload failed: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while uploading the file.');
                    });
            } else {
                // If no file is selected, save the data and redirect
                localStorage.setItem('dataRequestInfo', JSON.stringify(dataRequestInfo));
                window.location.href = 'attachments.php';
            }
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
            case 'publiccompany':
            case 'privatecompany':
                previousPage = 'org.php';
                break;
        }

        window.location.href = previousPage;
    });

    // Load saved data on page load
    document.addEventListener('DOMContentLoaded', loadDataRequestInfo);
</script>


<?php include 'footer.php'; ?>