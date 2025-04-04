<?php include 'header.php'; ?>

<div class="container mt-5 w-50">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Organization Information</h3>
            <p>Please provide the details of your organization.</p>

            <!-- Organization Information Form s-->
            <form id="orgInfoForm">
                <div class="form-group" id="kraPinField">
                    <label for="orgKraPin">Organization KRA PIN</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="orgKraPin" name="orgKraPin">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" id="verifyKraPinBtn">Verify</button>
                        </div>
                    </div>
                    <p id="kraPinError" class="text-danger"></p>
                </div>
                <div id="otherFields" style="display: none;">
                    <div class="form-group">
                        <label for="orgName">Organization Name:</label>
                        <input type="text" class="form-control" id="orgName" required disabled>
                    </div>
                    <div class="form-group">
                        <label for="orgPhone">Organization Phone Number:</label>
                        <input type="tel" class="form-control" id="orgPhone" required disabled>
                        <small id="phoneHelp" class="form-text text-muted">Please enter a 10-digit phone number.</small>
                    </div>
                    <div class="form-group">
                        <label for="orgEmail">Organization Email:</label>
                        <input type="email" class="form-control" id="orgEmail" required disbaled>
                        <small id="emailHelp" class="form-text text-muted">Please enter a valid email address.</small>
                    </div>
                </div>
                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary mt-4">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right mt-4">Next</button>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('verifyKraPinBtn').addEventListener('click', function() {
        const kraPin = document.getElementById('orgKraPin').value;
        const kraPinError = document.getElementById('kraPinError');

        // Validate KRA PIN (basic check for empty input)
        if (!kraPin) {
            kraPinError.innerText = "Please enter a KRA PIN.";
            return;
        }

        // Send KRA PIN to the server for validation
        fetch('validate_org_pin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'orgKraPin=' + encodeURIComponent(kraPin)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    kraPinError.innerText = ""; // Clear any previous error
                    document.getElementById('otherFields').style.display = 'block'; // Show other fields

                    // Enable all other fields
                    const otherFields = document.querySelectorAll('#otherFields input');
                    otherFields.forEach(field => field.disabled = false);

                    // Enable the 'Next' button if all required fields are filled
                    checkFormCompletion();
                } else {
                    kraPinError.innerText = data.message || "Invalid KRA PIN. Please try again.";
                }
            })
            .catch(error => {
                console.error('Error:', error);
                kraPinError.innerText = "An error occurred. Please try again.";
            });
    });

    function validateEmail(email) {
        const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return regex.test(email);
    }

    document.getElementById('orgEmail').addEventListener('blur', function() {
        const email = this.value;
        const emailHelp = document.getElementById('emailHelp');

        if (!validateEmail(email)) {
            emailHelp.textContent = "Please enter a valid email address.";
            emailHelp.classList.remove('text-muted');
            emailHelp.classList.add('text-danger');
            this.classList.add('is-invalid');
        } else {
            emailHelp.textContent = "Email address is valid.";
            emailHelp.classList.remove('text-danger');
            emailHelp.classList.add('text-success');
            this.classList.remove('is-invalid');
        }
    });

    function validatePhone(phone) {
        // Check if the phone number is exactly 10 digits and contains only numbers
        const regex = /^\d{10}$/;
        return regex.test(phone);
    }

    document.getElementById('orgPhone').addEventListener('blur', function() {
        const phone = this.value;
        const phoneHelp = document.getElementById('phoneHelp');

        if (!validatePhone(phone)) {
            phoneHelp.textContent = "Please enter a valid 10-digit phone number.";
            phoneHelp.classList.remove('text-muted');
            phoneHelp.classList.add('text-danger');
            this.classList.add('is-invalid');
        } else {
            phoneHelp.textContent = "Phone number is valid.";
            phoneHelp.classList.remove('text-danger');
            phoneHelp.classList.add('text-success');
            this.classList.remove('is-invalid');
        }
    });



    // Function to load saved organization details
    function loadOrgDetails() {
        let orgDetails = JSON.parse(localStorage.getItem('org_details')) || {};

        document.getElementById('orgName').value = orgDetails.orgName || '';
        document.getElementById('orgPhone').value = orgDetails.orgPhone || '';
        document.getElementById('orgEmail').value = orgDetails.orgEmail || '';
        document.getElementById('orgKraPin').value = orgDetails.orgKraPin || '';

        checkFormCompletion(); // Ensure button state is updated after loading
    }

    // Enable 'Next' button when all required fields are filled
    const requiredFields = document.querySelectorAll('#orgInfoForm input[required]');
    const nextBtn = document.getElementById('nextBtn');

    function checkFormCompletion() {
        let allFilled = Array.from(requiredFields).every(field => field.value.trim() !== '');
        nextBtn.disabled = !allFilled;
        return allFilled; // Ensures validation properly returns a boolean
    }

    // Attach input event listeners for validation
    requiredFields.forEach(field => {
        field.addEventListener('input', checkFormCompletion);
    });

    // Save organization details and move to the next page
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (!checkFormCompletion()) {
            alert("Please fill in all required fields before proceeding.");
            return;
        }

        let orgDetails = {
            orgName: document.getElementById('orgName').value.trim(),
            orgPhone: document.getElementById('orgPhone').value.trim(),
            orgEmail: document.getElementById('orgEmail').value.trim(),
            orgKraPin: document.getElementById('orgKraPin').value.trim()
        };

        localStorage.setItem('org_details', JSON.stringify(orgDetails));

        window.location.href = 'data_request.php'; // Redirect to the next page
    });

    // Back button navigation
    document.getElementById('backBtn').addEventListener('click', function() {
        window.location.href = 'personal_information.php'; // Redirect back to the previous step
    });

    // Load data on page load
    document.addEventListener('DOMContentLoaded', loadOrgDetails);
</script>

<?php include 'footer.php'; ?>