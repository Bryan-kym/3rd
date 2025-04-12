<?php 
include 'header.php'; 
require_once 'auth.php'; // Include your authentication functions
include 'config.php';

// Check if user is authenticated
try {
    $userId = authenticate(); // This will redirect if not authenticated
    
    // Get token from session or headers
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : 
             (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
} catch (Exception $e) {
    // Redirect to login if not authenticated
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>

<div class="container mt-5 w-75">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Data Request</h3>
            <p>Please fill in the details of your data request below.</p>

            <!-- Data Request Form -->
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
                            <input type="date" class="form-control" id="dateFrom" name="dateFrom" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dateTo">Date To</label>
                            <input type="date" class="form-control" id="dateTo" name="dateTo" max="<?php echo date('Y-m-d'); ?>" required disabled>
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
// Store token in localStorage if it came from session
const token = '<?php echo $token; ?>';
if (token && !localStorage.getItem('authToken')) {
    localStorage.setItem('authToken', token);
}

// Check if coming from proper flow by verifying required localStorage items
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
});

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

    if(input.length > maxChar){
        this.value = input.substring(0, maxChar);
    }
});

document.getElementById('dataDescription').addEventListener('input', function() {
    const maxChars = 50;
    let description = this.value;
    const descriptionHelp = document.getElementById('descriptionHelp');

    if (description.length > maxChars) {
        this.value = description.substring(0, maxChars);
        description = this.value;
    }

    descriptionHelp.textContent = `${description.length}/${maxChars} characters`;

    if (description.length >= maxChars) {
        descriptionHelp.classList.remove('text-muted', 'text-success');
        descriptionHelp.classList.add('text-danger');
    } else {
        descriptionHelp.classList.remove('text-danger');
        descriptionHelp.classList.add('text-muted');
    }
});

document.getElementById('requestReason').addEventListener('input', function() {
    const maxChars = 250;
    let requestReason = this.value;
    const charCount = document.getElementById('charCount');

    if (requestReason.length > maxChars) {
        this.value = requestReason.substring(0, maxChars);
        requestReason = this.value;
    }

    charCount.textContent = `${requestReason.length}/${maxChars} characters`;

    if (requestReason.length >= maxChars) {
        charCount.classList.remove('text-muted', 'text-success');
        charCount.classList.add('text-danger');
    } else {
        charCount.classList.remove('text-danger');
        charCount.classList.add('text-muted');
    }
});

// Improved date handling
document.getElementById('dateFrom').addEventListener('change', function() {
    const dateFrom = this.value;
    const dateTo = document.getElementById('dateTo');
    
    if (dateFrom) {
        dateTo.disabled = false;
        dateTo.min = dateFrom;
        dateTo.value = ''; // Clear previous end date when start date changes
    } else {
        dateTo.disabled = true;
        dateTo.value = '';
    }
});

document.getElementById('dateTo').addEventListener('change', function() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = this.value;
    
    if (dateFrom && dateTo && dateTo < dateFrom) {
        this.value = dateFrom; // Automatically set to dateFrom if invalid
    }
});

// Simplified validateDates function since we're now preventing invalid selections
function validateDates() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    if (!dateFrom || !dateTo) {
        alert("❌ Please select both date ranges.");
        return false;
    }
    return true;
}

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

        let fileInput = document.getElementById('dataTemplate');
        if ((fileInput && fileInput.files.length > 0) && fileInput.files[0].size > 0) {
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
                return response.text();
            })
            .then(text => {
                console.log("Raw response:", text);
                return JSON.parse(text);
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