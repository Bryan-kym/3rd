<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Attachments</h3>
            <p>Please upload the required documents for your category below.</p>

            <!-- Attachments Form -->
            <form id="attachmentsForm" method="post" enctype="multipart/form-data">
                <!-- Hidden inputs to hold additional data -->
                <input type="hidden" id="category" name="category" value="">
                <input type="hidden" id="other_description" name="other_description" value="">
                <input type="hidden" id="dataDescription" name="dataDescription" value="">
                <input type="hidden" id="specificFields" name="specificFields" value="">
                <input type="hidden" id="dateFrom" name="dateFrom" value="">
                <input type="hidden" id="dateTo" name="dateTo" value="">
                <input type="hidden" id="requestReason" name="requestReason" value="">
                <input type="hidden" id="surname" name="surname" value="">
                <input type="hidden" id="othernames" name="othernames" value="">
                <input type="hidden" id="email" name="email" value="">
                <input type="hidden" id="kra_pin" name="kra_pin" value="">
                <input type="hidden" id="phone" name="phone" value="">
                <input type="hidden" id="inst_name" name="inst_name" value="">
                <input type="hidden" id="inst_email" name="inst_email" value="">
                <input type="hidden" id="inst_phone" name="inst_phone" value="">
                <input type="hidden" id="orgName" name="orgName" value="">
                <input type="hidden" id="orgPhone" name="orgPhone" value="">
                <input type="hidden" id="orgEmail" name="orgEmail" value="">
                <input type="hidden" id="orgKraPin" name="orgKraPin" value="">
                <input type="hidden" id="taxagent_type" name="taxagent_type" value="">
                <input type="hidden" id="taxagent_type2" name="taxagent_type2" value="">
                <input type="hidden" id="taxagent_name2" name="taxagent_name2" value="">

                <!-- Attachment Sections -->
                 <!-- students -->
                <div id="studentAttachments" class="attachment-section">
                    <h5>Student Attachments</h5>
                    <div class="form-group">
                        <label for="nacostiPermit">Upload NACOSTI Permit</label>
                        <input type="file" class="form-control" id="nacostiPermit" name="nacostiPermit">
                    </div>
                    <div class="form-group">
                        <label for="introductionLetter">Letter of Introduction from Learning Institution</label>
                        <input type="file" class="form-control" id="introductionLetter" name="introductionLetter">
                    </div>
                </div>

                <!-- researchers -->
                <div id="researcherAttachments" class="attachment-section" >
                    <h5>Researcher Attachments</h5>
                    <div class="form-group">
                        <label for="nacostiPermitResearcher">Upload NACOSTI Permit</label>
                        <input type="file" class="form-control" id="nacostiPermitResearcher" name="nacostiPermitResearcher">
                    </div>
                    <div class="form-group">
                        <label for="introductionLetterResearcher">Letter of Introduction from Institution</label>
                        <input type="file" class="form-control" id="introductionLetterResearcher" name="introductionLetterResearcher">
                    </div>
                </div>

                <!-- tax payer -->
                <div id="taxpayerAttachments" class="attachment-section" >
                    <h5>Taxpayer Attachments</h5>
                    <div class="form-group">
                        <label for="idPassport">ID/Passport</label>
                        <input type="file" class="form-control" id="idPassport" name="idPassport">
                    </div>
                    <div class="form-group">
                        <label for="pinCertificate">PIN Certificate</label>
                        <input type="file" class="form-control" id="pinCertificate" name="pinCertificate">
                    </div>
                </div>

                <!-- tax agent -->
                <div id="taxAgentAttachments" class="attachment-section" >
                    <h5>Tax Agent Attachments</h5>
                    <div class="form-group">
                        <label for="consentLetter">Approved Consent Letter from Client</label>
                        <input type="file" class="form-control" id="consentLetter" name="consentLetter">
                    </div>
                </div>

                <!-- public company -->
                <div id="publicCompanyAttachments" class="attachment-section">
                    <h5>Public Company Attachments</h5>
                    <div class="form-group">
                        <label for="requestLetter">Request Letter from Authorized Signatory</label>
                        <input type="file" class="form-control" id="requestLetter" name="requestLetter">
                    </div>
                </div>

                <!-- private company -->
                <div id="privateCompanyAttachments" class="attachment-section">
                    <h5>Private Company Attachments</h5>
                    <div class="form-group">
                        <label for="requestLetterSignatoryOne">Request Letter from two Authorized Signatories</label>
                        <input type="file" class="form-control" id="requestLetterSignatoryOne" name="requestLetterSignatoryOne">
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="submit" id="submitBtn" class="btn btn-primary float-right">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get category and other values from localStorage and populate hidden inputs
    const category = localStorage.getItem('selectedCategory') || '';
    document.getElementById('category').value = category;
    // Populate other hidden fields as needed...

    // Show relevant attachment section based on the selected category
    showAttachmentSection(category);
});

// Function to show/hide attachment sections based on category
function showAttachmentSection(category) {
    console.log('Selected category:', category); // Debugging line
    const sections = {
        student : 'studentAttachments',
        researcher: 'researcherAttachments',
        taxpayer : 'taxpayerAttachments',
        taxagent : 'taxAgentAttachments',
        publiccompany: 'publicCompanyAttachments',
        privatecompany: 'privateCompanyAttachments'
    };

    // Hide all sections first
    document.querySelectorAll('.attachment-section').forEach(section => {
        section.style.display = 'none';
        section.querySelectorAll('input').forEach(input => input.required = false);
    });

    // Display the relevant section based on category
    const sectionId = sections[category];
    console.log('Section ID:', sectionId); // Debugging line
    if (sectionId) {
        const section = document.getElementById(sectionId);
        section.style.display = 'block';
        section.querySelectorAll('input').forEach(input => input.required = true);
    } else {
        console.warn('No section found for category:', category); // Debugging line
    }
}


document.addEventListener('DOMContentLoaded', function() {
    // Retrieve values from localStorage and populate hidden inputs
    const category = localStorage.getItem('selectedCategory') || '';
    const other_desc = localStorage.getItem('description') || '';
    const dataDescription = localStorage.getItem('dataDescription') || '';
    const specificFields = localStorage.getItem('specificFields') || '';
    const dateFrom = localStorage.getItem('dateFrom') || '';
    const dateTo = localStorage.getItem('dateTo') || '';
    const requestReason = localStorage.getItem('requestReason') || '';
    const surname = localStorage.getItem('surname') || '';
    const othernames = localStorage.getItem('othernames') || '';
    const email = localStorage.getItem('email') || '';
    const kra_pin = localStorage.getItem('kra_pin') || '';
    const phone = localStorage.getItem('phone') || '';
    const inst_name = localStorage.getItem('inst_name') || '';
    const inst_email = localStorage.getItem('inst_email') || '';
    const inst_phone = localStorage.getItem('inst_phone') || '';
    const orgName = localStorage.getItem('orgName') || '';
    const orgPhone = localStorage.getItem('orgPhone') || '';
    const orgEmail = localStorage.getItem('orgEmail') || '';
    const orgKraPin = localStorage.getItem('orgKraPin') || '';
    const taxagent_type = localStorage.getItem('userType') || '';
    const taxagent_type2 = localStorage.getItem('userType2') || '';
    const taxagent_name2 = localStorage.getItem('orgNameta') || '';

    // Set values to hidden fields
    document.getElementById('category').value = category;
    document.getElementById('other_description').value = other_desc;
    document.getElementById('dataDescription').value = dataDescription;
    document.getElementById('specificFields').value = specificFields;
    document.getElementById('dateFrom').value = dateFrom;
    document.getElementById('dateTo').value = dateTo;
    document.getElementById('requestReason').value = requestReason;
    document.getElementById('surname').value = surname;
    document.getElementById('othernames').value = othernames;
    document.getElementById('email').value = email;
    document.getElementById('kra_pin').value = kra_pin;
    document.getElementById('phone').value = phone;
    document.getElementById('inst_name').value = inst_name;
    document.getElementById('inst_email').value = inst_email;
    document.getElementById('inst_phone').value = inst_phone;
    document.getElementById('orgName').value = orgName;
    document.getElementById('orgPhone').value = orgPhone;
    document.getElementById('orgEmail').value = orgEmail;
    document.getElementById('orgKraPin').value = orgKraPin;
    document.getElementById('taxagent_type').value = taxagent_type;
    document.getElementById('taxagent_type2').value = taxagent_type2;
    document.getElementById('taxagent_name2').value = taxagent_name2;

    // Call the function to show the correct attachment section
    showAttachmentSection(category);
});



// Navigation back to the data request page
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'data_request.php';
});

// Handle form submission
document.getElementById('attachmentsForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    // Create FormData object
    const formData = new FormData(this);

    fetch('submit_attachments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            alert('Form submitted successfully!');
            localStorage.clear();
            window.location.href = 'index.php'; // Redirect on success
        } else {
            alert('Error submitting attachments. Please try again.');
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});
</script>

<?php include 'footer.php'; ?>
