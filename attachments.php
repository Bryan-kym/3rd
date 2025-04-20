<?php 
include 'header.php'; 
require_once 'auth.php';

try {
    $userId = authenticate();    
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : 
             (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
} catch (Exception $e) {
    $_SESSION['auth_message'] = 'Please login to continue';
    $_SESSION['auth_message_type'] = 'error';
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>

<div class="attachments-container">
    <div class="attachments-card">
        <div class="attachments-header">
            <h2>Upload Attachments</h2>
            <p>Please upload the required files before proceeding.</p>
        </div>

        <!-- Notification element -->
        <div class="notification" id="notification"></div>

        <form id="attachmentsForm" enctype="multipart/form-data">
            <div id="attachmentsContainer">
                <!-- Dynamic attachment fields will be added here -->
            </div>

            <div id="previewSection" class="preview-section">
                <h5>Selected Files</h5>
                <div id="filePreviewList" class="file-preview-list"></div>
            </div>

            <div id="errorMessage" class="error-message"></div>

            <div class="form-actions">
                <button type="button" id="backBtn" class="btn-outline">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <button type="submit" id="submitBtn" class="btn-primary">
                    <span id="submitText">Submit</span>
                    <span class="loading-spinner" id="submitSpinner"></span>
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
        --success-color: #28a745;
        --error-color: #dc3545;
        --border-radius: 0.5rem;
        --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    .attachments-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 120px);
        padding: 2rem;
        background-color: var(--light-grey);
    }

    .attachments-card {
        background: var(--light-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 800px;
        padding: 2.5rem;
    }

    .attachments-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .attachments-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .attachments-header p {
        color: var(--grey-color);
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 500;
        color: var(--secondary-color);
    }

    .file-upload-wrapper {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .file-upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem;
        border: 2px dashed #ddd;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
    }

    .file-upload-label:hover {
        border-color: var(--primary-color);
        background-color: var(--primary-light);
    }

    .file-upload-label i {
        font-size: 2rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .file-upload-label span {
        color: var(--grey-color);
    }

    .file-upload-label .file-name {
        margin-top: 0.5rem;
        font-weight: 500;
        color: var(--secondary-color);
    }

    .file-upload-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }

    .preview-section {
        margin: 2rem 0;
        padding: 1.5rem;
        background-color: var(--light-grey);
        border-radius: var(--border-radius);
        display: none;
    }

    .preview-section h5 {
        margin-bottom: 1rem;
        color: var(--secondary-color);
    }

    .file-preview-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .file-preview-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        background-color: var(--light-color);
        border-radius: var(--border-radius);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .file-preview-item i {
        margin-right: 0.75rem;
        color: var(--primary-color);
    }

    .file-preview-info {
        flex: 1;
    }

    .file-preview-name {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .file-preview-type {
        font-size: 0.8rem;
        color: var(--grey-color);
    }

    .file-preview-remove {
        color: var(--error-color);
        cursor: pointer;
        margin-left: 1rem;
    }

    .error-message {
        color: var(--error-color);
        margin: 1rem 0;
        text-align: center;
        min-height: 1.5rem;
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

    .loading-spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top: 3px solid white;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Notification styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 1000;
        transform: translateX(200%);
        transition: var(--transition);
        opacity: 0;
    }

    .notification.show {
        transform: translateX(0);
        opacity: 1;
    }

    .notification.success {
        background-color: var(--success-color);
        color: white;
    }

    .notification.error {
        background-color: var(--error-color);
        color: white;
    }

    .notification-icon {
        font-size: 1.25rem;
    }

    @media (max-width: 768px) {
        .attachments-card {
            padding: 1.5rem;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn-primary, .btn-outline {
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

// Show notification function
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'}"></i>
        </div>
        <div class="notification-message">${message}</div>
    `;
    notification.className = `notification ${type}`;
    notification.classList.add('show');
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 5000);
}

// Check for any session messages on load
function checkForSessionMessages() {
    const message = '<?php echo isset($_SESSION['auth_message']) ? $_SESSION['auth_message'] : '' ?>';
    const messageType = '<?php echo isset($_SESSION['auth_message_type']) ? $_SESSION['auth_message_type'] : '' ?>';
    
    if (message) {
        showNotification(message, messageType);
        <?php unset($_SESSION['auth_message']); ?>
        <?php unset($_SESSION['auth_message_type']); ?>
    }
}

// Attachment requirements by category
const attachmentRequirements = {
    "student": ["NACOSTI Permit", "Letter of Introduction"],
    "researcher": ["NACOSTI Permit", "Letter of Introduction"],
    "taxpayer": ["ID", "PIN Certificate"],
    "taxagent": ["Approved Consent Letter"],
    "publiccompany": ["Request Letter from Authorized Signatory"],
    "privatecompany": ["Request Letter from Two Authorized Signatories"]
};

// Initialize the page
window.addEventListener('load', async function() {
    checkForSessionMessages();
    
    // Check for auth token and required flow items
    if (!localStorage.getItem('authToken') || 
        !localStorage.getItem('nda_form') || 
        !localStorage.getItem('selectedCategory') ||
        !localStorage.getItem('dataRequestInfo')) {
        showNotification('Please complete the previous steps first', 'error');
        setTimeout(() => window.location.href = 'dashboard.php', 2000);
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
            localStorage.removeItem('dataRequestInfo');
            showNotification('Session expired, please login again', 'error');
            setTimeout(() => window.location.href = 'login.html', 2000);
        }
    } catch (error) {
        console.error('Token validation error:', error);
        showNotification('An error occurred, please try again', 'error');
        setTimeout(() => window.location.href = 'login.html', 2000);
    }

    // Initialize the form
    const selectedCategory = localStorage.getItem("selectedCategory") || "";
    populateAttachments(selectedCategory);
});

function populateAttachments(category) {
    const attachmentsContainer = document.getElementById("attachmentsContainer");
    attachmentsContainer.innerHTML = "";

    if (attachmentRequirements[category]) {
        attachmentRequirements[category].forEach(req => {
            const fileId = `file-${req.replace(/\s+/g, '-').toLowerCase()}`;
            const wrapper = document.createElement("div");
            wrapper.className = "file-upload-wrapper";
            wrapper.innerHTML = `
                <label for="${fileId}" class="file-upload-label">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>Click to upload ${req}</span>
                    <span class="file-name" id="${fileId}-name">No file selected</span>
                    <input type="file" id="${fileId}" class="file-upload-input" 
                        name="attachments[]" data-name="${req}" 
                        accept=".pdf,.jpg,.jpeg,.png" required>
                </label>
            `;
            attachmentsContainer.appendChild(wrapper);

            // Add event listener for file selection
            document.getElementById(fileId).addEventListener('change', function(e) {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
                document.getElementById(`${fileId}-name`).textContent = fileName;
                updateFilePreviews();
                validateForm();
            });
        });
    }
}

function updateFilePreviews() {
    const filePreviewList = document.getElementById("filePreviewList");
    const previewSection = document.getElementById("previewSection");
    const fileInputs = document.querySelectorAll(".file-upload-input");
    
    filePreviewList.innerHTML = "";
    let hasFiles = false;

    fileInputs.forEach(input => {
        const file = input.files[0];
        if (file) {
            hasFiles = true;
            const fileType = file.name.split('.').pop().toLowerCase();
            const fileSize = (file.size / (1024 * 1024)).toFixed(2); // in MB
            
            const previewItem = document.createElement("div");
            previewItem.className = "file-preview-item";
            previewItem.innerHTML = `
                <i class="bi ${getFileIcon(fileType)}"></i>
                <div class="file-preview-info">
                    <div class="file-preview-name">${input.dataset.name}</div>
                    <div class="file-preview-type">${file.name} (${fileSize} MB)</div>
                </div>
                <i class="bi bi-x-circle file-preview-remove" data-for="${input.id}"></i>
            `;
            filePreviewList.appendChild(previewItem);
        }
    });

    // Add event listeners to remove buttons
    document.querySelectorAll('.file-preview-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const inputId = this.getAttribute('data-for');
            const input = document.getElementById(inputId);
            input.value = '';
            document.getElementById(`${inputId}-name`).textContent = 'No file selected';
            updateFilePreviews();
            validateForm();
        });
    });

    previewSection.style.display = hasFiles ? "block" : "none";
}

function getFileIcon(fileType) {
    switch(fileType) {
        case 'pdf': return 'bi-file-earmark-pdf';
        case 'jpg':
        case 'jpeg':
        case 'png': return 'bi-file-image';
        default: return 'bi-file-earmark';
    }
}

function validateForm() {
    const submitBtn = document.getElementById('submitBtn');
    const errorMessage = document.getElementById("errorMessage");
    let isValid = true;

    // Check all required files are uploaded
    document.querySelectorAll(".file-upload-input[required]").forEach(input => {
        if (!input.files[0]) {
            isValid = false;
        }
    });

    if (!isValid) {
        errorMessage.textContent = "Please upload all required files";
        submitBtn.disabled = true;
    } else {
        errorMessage.textContent = "";
        submitBtn.disabled = false;
    }

    return isValid;
}

// Form submission handler
document.getElementById("attachmentsForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    if (!validateForm()) return;

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const spinner = document.getElementById('submitSpinner');
    const errorMessage = document.getElementById("errorMessage");

    // Show loading state
    submitBtn.disabled = true;
    submitText.textContent = "Processing...";
    spinner.style.display = 'inline-block';

    // Prepare form data
    const formData = new FormData();
    const selectedCategory = localStorage.getItem("selectedCategory") || "";
    const userEmail = localStorage.getItem("userEmail") || "";

    // Add all stored data to form submission
    const storedData = {
        personalInfo: JSON.parse(localStorage.getItem("personalInfo") || "{}"),
        dataRequestInfo: JSON.parse(localStorage.getItem("dataRequestInfo") || "{}"),
        taxAgentInfo: JSON.parse(localStorage.getItem("taxAgentInfo") || "{}"),
        orgDetails: JSON.parse(localStorage.getItem("org_details") || "{}"),
        instDetails: JSON.parse(localStorage.getItem("ins_details") || "{}"),
        clientInfo: JSON.parse(localStorage.getItem("clientInfo") || "{}"),
        ndaForm: localStorage.getItem("nda_form"),
        category: selectedCategory,
        userEmail: userEmail
    };

    formData.append("requestData", JSON.stringify(storedData));

    // Add attachments
    document.querySelectorAll(".file-upload-input").forEach(input => {
        const file = input.files[0];
        if (file) {
            const attachmentName = input.dataset.name;
            const fileType = file.name.split('.').pop().toLowerCase();
            const uniqueFilename = `${selectedCategory}_${attachmentName.replace(/\s+/g, '_')}_${Date.now()}.${fileType}`;
            
            formData.append("attachments[]", file, uniqueFilename);
            formData.append("attachmentNames[]", attachmentName);
        }
    });

    try {
        const response = await fetch("submit_attachments.php", {
            method: "POST",
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('authToken')
            },
            body: formData
        });

        if (response.status === 401) {
            showNotification('Session expired, please login again', 'error');
            setTimeout(() => window.location.href = 'api/login.php?session_expired=1', 2000);
            return;
        }

        const data = await response.json();

        if (data.success) {
            showNotification('Submission successful! You will receive a confirmation email shortly.', 'success');
            
            // Clear only the relevant localStorage items
            const itemsToKeep = ['authToken', 'userEmail'];
            const itemsToRemove = Object.keys(localStorage).filter(
                key => !itemsToKeep.includes(key)
            );
            itemsToRemove.forEach(key => localStorage.removeItem(key));
            
            setTimeout(() => window.location.href = "dashboard.php", 3000);
        } else {
            showNotification(data.error || "Submission failed. Please try again.", 'error');
            submitBtn.disabled = false;
            submitText.textContent = "Submit";
            spinner.style.display = 'none';
        }
    } catch (error) {
        console.error("Error:", error);
        showNotification("An unexpected error occurred. Please try again.", 'error');
        submitBtn.disabled = false;
        submitText.textContent = "Submit";
        spinner.style.display = 'none';
    }
});

// Back button navigation
document.getElementById("backBtn").addEventListener("click", function() {
    window.location.href = "data_request.php";
});
</script>

<?php include 'footer.php'; ?>