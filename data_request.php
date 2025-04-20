<?php
ob_start();
include 'header.php';
require_once 'auth.php';
include 'config.php';

try {
    $userId = authenticate();
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
} catch (Exception $e) {
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>

<div class="data-request-container">
    <div class="data-request-card">
        <div class="data-request-header">
            <h2>Data Request</h2>
            <p>Please fill in the details of your data request below.</p>
        </div>

        <form id="dataRequestForm" method="POST" action="your_submission_handler.php" enctype="multipart/form-data">
            <input type="hidden" name="category" id="category" value="">

            <div class="form-group">
                <label for="dataDescription">Description of the Data Requested</label>
                <input type="text" class="form-input" id="dataDescription" name="dataDescription" required>
                <div class="character-counter"><span id="descriptionCount">0</span>/50 characters</div>
            </div>

            <div class="form-group">
                <label>Select Specific Fields:</label>
                <div class="field-options">
                    <span class="field-option" data-value="KRA Pin">KRA Pin</span>
                    <span class="field-option" data-value="Taxpayer Name">Taxpayer Name</span>
                    <span class="field-option" data-value="Station">Station</span>
                    <span class="field-option" data-value="Amount">Amount</span>
                </div>
                <input type="text" class="form-input" id="specificFields" name="specificFields" placeholder="Selected fields will appear here" readonly>
                <div class="character-counter"><span id="fieldsCount">0</span>/150 characters</div>
            </div>

            <div class="form-group">
                <label for="dataTemplate">Upload Specific Template (Optional)</label>
                <div class="file-upload">
                    <label for="dataTemplate" class="file-upload-label">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Choose file</span>
                        <span id="fileName">No file selected</span>
                    </label>
                    <input type="file" id="dataTemplate" name="dataTemplate" accept=".pdf,.doc,.docx,.xls,.xlsx">
                </div>
            </div>

            <div class="date-range">
                <div class="form-group">
                    <label for="dateFrom">Date From</label>
                    <input type="date" class="form-input" id="dateFrom" name="dateFrom" required>
                </div>
                <div class="form-group">
                    <label for="dateTo">Date To</label>
                    <input type="date" class="form-input" id="dateTo" name="dateTo" disabled required>
                </div>
            </div>

            <div class="form-group">
                <label for="requestReason">Reason for Requesting Data</label>
                <textarea class="form-textarea" id="requestReason" name="requestReason" rows="4" required></textarea>
                <div class="character-counter"><span id="reasonCount">0</span>/250 characters</div>
            </div>

            <div class="form-actions">
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
        --transition: all 0.3s ease;
    }

    .data-request-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 120px);
        padding: 2rem;
        background-color: var(--light-grey);
    }

    .data-request-card {
        background: var(--light-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 800px;
        padding: 2.5rem;
    }

    .data-request-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .data-request-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .data-request-header p {
        color: var(--grey-color);
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--secondary-color);
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        transition: var(--transition);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(217, 35, 46, 0.15);
    }

    .form-textarea {
        width: 100%;
        padding: 1rem;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        resize: vertical;
        min-height: 100px;
        transition: var(--transition);
    }

    .form-textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(217, 35, 46, 0.15);
    }

    .character-counter {
        text-align: right;
        font-size: 0.8rem;
        color: var(--grey-color);
        margin-top: 0.25rem;
    }

    .character-counter.warning {
        color: var(--primary-color);
    }

    .field-options {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .field-option {
        padding: 0.5rem 1rem;
        background-color: var(--light-grey);
        border-radius: 20px;
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.9rem;
    }

    .field-option:hover {
        background-color: var(--primary-light);
        color: var(--primary-color);
    }

    .field-option.selected {
        background-color: var(--primary-color);
        color: white;
    }

    .file-upload {
        position: relative;
        margin-top: 0.5rem;
    }

    .file-upload-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        border: 2px dashed #ddd;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
    }

    .file-upload-label:hover {
        border-color: var(--primary-color);
        background-color: rgba(217, 35, 46, 0.05);
    }

    .file-upload-label i {
        font-size: 1.5rem;
        color: var(--primary-color);
    }

    .file-upload-label span:last-child {
        margin-left: auto;
        color: var(--grey-color);
        font-size: 0.9rem;
    }

    .file-upload input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }

    .date-range {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
    }

    /* Button Styles */
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
        .data-request-card {
            padding: 1.5rem;
        }

        .date-range {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
            gap: 1rem;
        }

        .btn-primary,
        .btn-outline {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // Store token in localStorage if it came from session
    const token = '<?php echo $token; ?>';
    if (token && !localStorage.getItem('authToken')) {
        localStorage.setItem('authToken', token);
    }

    // Check form validity and enable/disable Next button
    function checkFormValidity() {
        const dataDescription = document.getElementById('dataDescription').value.trim();
        const specificFields = document.getElementById('specificFields').value.trim();
        const dateFrom = document.getElementById('dateFrom').value.trim();
        const dateTo = document.getElementById('dateTo').value.trim();
        const requestReason = document.getElementById('requestReason').value.trim();

        const isValid = dataDescription &&
            specificFields &&
            dateFrom &&
            dateTo &&
            requestReason;

        document.getElementById('nextBtn').disabled = !isValid;
    }

    // Initialize form validation on page load
    window.addEventListener('load', async function() {
        // Check for auth token and required flow items
        if (!localStorage.getItem('authToken') ||
            !localStorage.getItem('nda_form') ||
            !localStorage.getItem('selectedCategory')) {
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
                localStorage.removeItem('selectedCategory');
                localStorage.removeItem('personalInfo');
                localStorage.removeItem('org_details');
                localStorage.removeItem('clientInfo');
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Token validation error:', error);
            window.location.href = 'login.html';
        }

        // Set the category value
        document.getElementById('category').value = localStorage.getItem('selectedCategory');

        // Load any previously saved form data
        loadDataRequestInfo();

        // Initialize date pickers
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dateFrom').max = today;
        document.getElementById('dateTo').max = today;

        // Set up event listeners for form validation
        setupFormValidation();
    });

    function setupFormValidation() {
        // Add event listeners to all required fields
        document.getElementById('dataDescription').addEventListener('input', checkFormValidity);
        document.getElementById('specificFields').addEventListener('input', checkFormValidity);
        document.getElementById('dateFrom').addEventListener('change', checkFormValidity);
        document.getElementById('dateTo').addEventListener('change', checkFormValidity);
        document.getElementById('requestReason').addEventListener('input', checkFormValidity);

        // Initial check
        checkFormValidity();
    }

    // Function to load saved data into form fields
    function loadDataRequestInfo() {
        let dataRequestInfo = JSON.parse(localStorage.getItem('dataRequestInfo')) || {};

        document.getElementById('dataDescription').value = dataRequestInfo.dataDescription || '';
        document.getElementById('specificFields').value = dataRequestInfo.specificFields || '';
        document.getElementById('dateFrom').value = dataRequestInfo.dateFrom || '';
        document.getElementById('dateTo').value = dataRequestInfo.dateTo || '';
        document.getElementById('requestReason').value = dataRequestInfo.requestReason || '';

        // Enable dateTo if dateFrom has a value
        if (dataRequestInfo.dateFrom) {
            document.getElementById('dateTo').disabled = false;
            document.getElementById('dateTo').min = dataRequestInfo.dateFrom;
        }

        // Update character counters
        updateCharacterCounters();
    }

    function updateCharacterCounters() {
        // Update description counter
        const descCount = document.getElementById('dataDescription').value.length;
        document.getElementById('descriptionCount').textContent = descCount;
        document.getElementById('descriptionCount').parentElement.classList.toggle('warning', descCount >= 50);

        // Update fields counter
        const fieldsCount = document.getElementById('specificFields').value.length;
        document.getElementById('fieldsCount').textContent = fieldsCount;
        document.getElementById('fieldsCount').parentElement.classList.toggle('warning', fieldsCount >= 150);

        // Update reason counter
        const reasonCount = document.getElementById('requestReason').value.length;
        document.getElementById('reasonCount').textContent = reasonCount;
        document.getElementById('reasonCount').parentElement.classList.toggle('warning', reasonCount >= 250);
    }

    // Enhanced field selection
    document.querySelectorAll('.field-option').forEach(option => {
        option.addEventListener('click', function() {
            const fieldInput = document.getElementById('specificFields');
            const selectedFields = fieldInput.value ? fieldInput.value.split(', ') : [];
            const newValue = this.getAttribute('data-value');

            if (!selectedFields.includes(newValue)) {
                selectedFields.push(newValue);
                fieldInput.value = selectedFields.join(', ');
                this.classList.add('selected');
            } else {
                const index = selectedFields.indexOf(newValue);
                selectedFields.splice(index, 1);
                fieldInput.value = selectedFields.join(', ');
                this.classList.remove('selected');
            }

            // Update character count
            const fieldsCount = document.getElementById('fieldsCount');
            fieldsCount.textContent = fieldInput.value.length;
            fieldsCount.parentElement.classList.toggle('warning', fieldInput.value.length >= 150);

            checkFormValidity();
        });
    });

    // Enhanced character counting
    document.getElementById('dataDescription').addEventListener('input', function() {
        const maxChars = 50;
        let description = this.value;

        if (description.length > maxChars) {
            this.value = description.substring(0, maxChars);
            description = this.value;
        }

        const counter = document.getElementById('descriptionCount');
        counter.textContent = description.length;
        counter.parentElement.classList.toggle('warning', description.length >= maxChars);

        checkFormValidity();
    });

    document.getElementById('requestReason').addEventListener('input', function() {
        const maxChars = 250;
        let requestReason = this.value;

        if (requestReason.length > maxChars) {
            this.value = requestReason.substring(0, maxChars);
            requestReason = this.value;
        }

        const counter = document.getElementById('reasonCount');
        counter.textContent = requestReason.length;
        counter.parentElement.classList.toggle('warning', requestReason.length >= maxChars);

        checkFormValidity();
    });

    // Improved date handling
    document.getElementById('dateFrom').addEventListener('change', function() {
        const dateFrom = this.value;
        const dateTo = document.getElementById('dateTo');

        if (dateFrom) {
            dateTo.disabled = false;
            dateTo.min = dateFrom;
            dateTo.value = '';
        } else {
            dateTo.disabled = true;
            dateTo.value = '';
        }

        checkFormValidity();
    });

    document.getElementById('dateTo').addEventListener('change', function() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = this.value;

        if (dateFrom && dateTo && dateTo < dateFrom) {
            this.value = dateFrom;
        }

        checkFormValidity();
    });

    // Validate all required fields
    function validateForm() {
        const dataDescription = document.getElementById('dataDescription').value.trim();
        const specificFields = document.getElementById('specificFields').value.trim();
        const dateFrom = document.getElementById('dateFrom').value.trim();
        const dateTo = document.getElementById('dateTo').value.trim();
        const requestReason = document.getElementById('requestReason').value.trim();

        if (!dataDescription || !specificFields || !dateFrom || !dateTo || !requestReason) {
            alert("Please fill in all required fields before proceeding.");
            return false;
        }
        return true;
    }

    // Next button click handler
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (validateForm()) {
            let dataRequestInfo = {
                dataDescription: document.getElementById('dataDescription').value,
                specificFields: document.getElementById('specificFields').value,
                dateFrom: document.getElementById('dateFrom').value,
                dateTo: document.getElementById('dateTo').value,
                requestReason: document.getElementById('requestReason').value
            };

            let fileInput = document.getElementById('dataTemplate');
            if (fileInput.files.length > 0) {
                let formData = new FormData();
                formData.append('file', fileInput.files[0]);

                // Generate a unique file name
                let userName = localStorage.getItem('userName') || 'user';
                let fileName = fileInput.files[0].name;
                let newFileName = userName + '_template_' + Date.now() + '_' + fileName;
                let fileType = newFileName.split('.').pop().toLowerCase();
                formData.append('fileName', newFileName);

                // Upload the file to the server with auth token
                fetch('upload_template.php', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                        },
                        body: formData
                    })
                    .then(response => {
                        if (response.status === 401) {
                            window.location.href = 'api/login.php?session_expired=1';
                            return;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            dataRequestInfo.templatePath = data.filePath;
                            dataRequestInfo.templateFileType = fileType;
                            localStorage.setItem('dataRequestInfo', JSON.stringify(dataRequestInfo));
                            window.location.href = 'attachments.php';
                        } else {
                            alert('File upload failed: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while uploading the file.');
                    });
            } else {
                localStorage.setItem('dataRequestInfo', JSON.stringify(dataRequestInfo));
                window.location.href = 'attachments.php';
            }
        }
    });

    // Back button click handler
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

    // File upload display
    document.getElementById('dataTemplate').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
        document.getElementById('fileName').textContent = fileName;
    });
</script>

<?php include 'footer.php';
ob_flush(); ?>