<?php 
include 'header.php'; 
require_once 'auth.php'; // Include your authentication functions

// Check if user is authenticated
try {
    $userId = authenticate(); // This will redirect if not authenticated
    
    // Get token from session or headers
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : 
             (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
} catch (Exception $e) {
    // Redirect to login if not authenticated
    $_SESSION['auth_message'] = 'Please login to continue';
    $_SESSION['auth_message_type'] = 'error';
    header('Location: login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Upload Attachments</h3>
            <p>Please upload the required files before proceeding.</p>

            <!-- Notification element -->
            <div class="notification" id="notification"></div>

            <form id="attachmentsForm" enctype="multipart/form-data">
                <div id="attachmentsContainer">
                    <!-- Dynamic attachment fields will be added here -->
                </div>

                <div id="previewSection" style="display: none;">
                    <h5>File Previews</h5>
                    <ul id="filePreviewList"></ul>
                </div>

                <div id="errorMessage" class="mt-3 text-danger"></div>

                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="submit" id="submitBtn" class="btn btn-primary float-right">
                    Submit
                    <span class="loading" id="submitSpinner" style="display: none;"></span>
                </button>
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

// Show notification function (same as dashboard.php)
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = 'notification ' + type;
    notification.classList.add('show');
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 5000);
}

// Check for any session messages on load
function checkForSessionMessages() {
    const notification = document.getElementById('notification');
    const message = '<?php echo isset($_SESSION['auth_message']) ? $_SESSION['auth_message'] : '' ?>';
    const messageType = '<?php echo isset($_SESSION['auth_message_type']) ? $_SESSION['auth_message_type'] : '' ?>';
    
    if (message) {
        showNotification(message, messageType);
        <?php unset($_SESSION['auth_message']); ?>
        <?php unset($_SESSION['auth_message_type']); ?>
    }
}

// Check if coming from proper flow by verifying required localStorage items
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

// Attachment requirements by category
const attachmentRequirements = {
    "student": ["NACOSTI Permit", "Letter of Introduction"],
    "researcher": ["NACOSTI Permit", "Letter of Introduction"],
    "taxpayer": ["ID", "PIN Certificate"],
    "taxagent": ["Approved Consent Letter"],
    "publiccompany": ["Request Letter from Authorized Signatory"],
    "privatecompany": ["Request Letter from Two Authorized Signatories"]
};

function populateAttachments(category) {
    const attachmentsContainer = document.getElementById("attachmentsContainer");
    const filePreviewList = document.getElementById("filePreviewList");
    
    attachmentsContainer.innerHTML = "";
    filePreviewList.innerHTML = "";

    if (attachmentRequirements[category]) {
        attachmentRequirements[category].forEach(req => {
            const inputDiv = document.createElement("div");
            inputDiv.classList.add("form-group");
            inputDiv.innerHTML = `
                <label>${req} (PDF, JPG, PNG)</label>
                <input type="file" name="attachments[]" class="form-control attachmentFile" data-name="${req}" data-document-type="${req}" accept=".pdf,.jpg,.png" required>
            `;
            attachmentsContainer.appendChild(inputDiv);
        });
    }
}

function handleFilePreview() {
    const filePreviewList = document.getElementById("filePreviewList");
    const previewSection = document.getElementById("previewSection");
    const files = document.querySelectorAll(".attachmentFile");
    let hasFiles = false;

    filePreviewList.innerHTML = "";

    files.forEach(fileInput => {
        const file = fileInput.files[0];
        if (file) {
            hasFiles = true;
            const li = document.createElement("li");
            li.textContent = `${fileInput.dataset.name}: ${file.name}`;
            filePreviewList.appendChild(li);
        }
    });
    previewSection.style.display = hasFiles ? "block" : "none";
}

// Event listener for file previews
document.getElementById("attachmentsContainer").addEventListener("change", handleFilePreview);

// Form submission handler
document.getElementById("attachmentsForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const spinner = document.getElementById('submitSpinner');
    const errorMessage = document.getElementById("errorMessage");
    errorMessage.textContent = "";

    // Show loading state
    submitBtn.disabled = true;
    spinner.style.display = 'inline-block';

    // Validate all required files are uploaded
    let valid = true;
    document.querySelectorAll(".attachmentFile").forEach(input => {
        if (!input.files[0]) {
            valid = false;
            errorMessage.textContent = "All required files must be uploaded.";
        }
    });
    
    if (!valid) {
        submitBtn.disabled = false;
        spinner.style.display = 'none';
        return;
    }

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
    document.querySelectorAll(".attachmentFile").forEach(input => {
        const file = input.files[0];
        if (file) {
            const attachmentName = input.getAttribute("data-name");
            const fileType = file.name.split('.').pop().toLowerCase();
            
            // Generate unique filename
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
        }
    } catch (error) {
        console.error("Error:", error);
        showNotification("An unexpected error occurred. Please try again.", 'error');
    } finally {
        spinner.style.display = 'none';
    }
});

// Back button navigation
document.getElementById("backBtn").addEventListener("click", function() {
    window.location.href = "data_request.php";
});
</script>

<style>
/* Notification styles (same as dashboard.php) */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    background: #28a745;
    color: white;
    border-radius: 5px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    transform: translateX(200%);
    transition: transform 0.3s ease;
    z-index: 1000;
}

.notification.show {
    transform: translateX(0);
}

.notification.error {
    background: #dc3545;
}

.notification.info {
    background: #17a2b8;
}

.loading {
    display: inline-block;
    margin-left: 10px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top: 3px solid white;
    width: 16px;
    height: 16px;
    animation: spin 1s linear infinite;
    vertical-align: middle;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<?php include 'footer.php'; ?>