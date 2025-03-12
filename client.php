<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Client Information</h3>
            <p>Please fill in your information below.</p>

            <!-- Personal Information Form c-->
            <form id="step3Form">
                <!-- Dropdown to select Individual or Organization -->
                <div class="form-group">
                    <label for="userType2">Is the client an Individual or an Organization? Choose from the options below</label>
                    <select class="form-control" id="userType2" name="userType2" required>
                        <option value="individual">Individual</option>
                        <option value="organization">Organization</option>
                    </select>
                </div>

                <!-- Fields for Individual -->
                <div id="individualFields">
                    <div class="form-group" id="kraPinField">
                        <label for="kra_pin">KRA PIN</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="kra_pin" name="kra_pin">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="verifyPinBtn">Verify</button>
                            </div>
                        </div>
                        <p id="kraPinError" class="text-danger"></p>
                    </div>
                    <div id="otherFields" style="display: none;">
                        <div class="form-group">
                            <label for="surname">Surname</label>
                            <input type="text" class="form-control" id="surname" name="surname" required>
                        </div>

                        <div class="form-group">
                            <label for="othernames">Other Names</label>
                            <input type="text" class="form-control" id="othernames" name="othernames" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <small id="emailHelp" class="form-text text-muted">Please enter a valid email address.</small>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                            <small id="phoneHelp" class="form-text text-muted">Please enter a 10-digit phone number.</small>
                        </div>
                    </div>
                </div>

                <!-- Fields for Organization (Hidden initially) -->
                <div id="organizationFields" style="display: none;">
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
                    <div id="otherFieldsorg" style="display: none;">
                        <div class="form-group">
                            <label for="orgName">Organization Name</label>
                            <input type="text" class="form-control" id="orgName" name="orgName" required>
                        </div>
                        <div class="form-group">
                            <label for="orgPhone">Organization Phone Number</label>
                            <input type="tel" class="form-control" id="orgPhone" name="orgPhone" required>
                            <small id="phoneHelporg" class="form-text text-muted">Please enter a 10-digit phone number.</small>
                        </div>
                        <div class="form-group">
                            <label for="orgEmail">Organization Email</label>
                            <input type="email" class="form-control" id="orgEmail" name="orgEmail" required>
                            <small id="emailHelporg" class="form-text text-muted">Please enter a valid email address.</small>
                        </div>
                    </div>

                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Function to toggle fields based on selected option
    function toggleFields() {
        const userType = document.getElementById('userType2').value;
        document.getElementById('individualFields').style.display = userType === 'individual' ? 'block' : 'none';
        document.getElementById('organizationFields').style.display = userType === 'organization' ? 'block' : 'none';
    }

    // Function to check if all required fields are filled
    function checkFormCompletion() {
        const userType = document.getElementById('userType2').value;
        const requiredFields = document.querySelectorAll(`#${userType}Fields input[required]`);
        nextBtn.disabled = !Array.from(requiredFields).every(field => field.value.trim() !== '');
    }

    // Function to store data in localStorage
    function saveClientInfo() {
        const userType = document.getElementById('userType2').value;
        let clientInfo = {
            userType
        };

        if (userType === 'individual') {
            clientInfo.surname = document.getElementById('surname').value;
            clientInfo.othernames = document.getElementById('othernames').value;
            clientInfo.email = document.getElementById('email').value;
            clientInfo.phone = document.getElementById('phone').value;
            clientInfo.kra_pin = document.getElementById('kra_pin').value;
        } else {
            clientInfo.orgName = document.getElementById('orgName').value;
            clientInfo.orgPhone = document.getElementById('orgPhone').value;
            clientInfo.orgEmail = document.getElementById('orgEmail').value;
            clientInfo.orgKraPin = document.getElementById('orgKraPin').value;
        }

        localStorage.setItem('clientInfo', JSON.stringify(clientInfo));
    }

    // Function to load stored data into form fields
    function loadClientInfo() {
        const savedData = localStorage.getItem('clientInfo');
        if (savedData) {
            const clientInfo = JSON.parse(savedData);
            document.getElementById('userType2').value = clientInfo.userType;
            toggleFields();

            if (clientInfo.userType === 'individual') {
                document.getElementById('surname').value = clientInfo.surname || '';
                document.getElementById('othernames').value = clientInfo.othernames || '';
                document.getElementById('email').value = clientInfo.email || '';
                document.getElementById('phone').value = clientInfo.phone || '';
                document.getElementById('kra_pin').value = clientInfo.kra_pin || '';
            } else {
                document.getElementById('orgName').value = clientInfo.orgName || '';
                document.getElementById('orgPhone').value = clientInfo.orgPhone || '';
                document.getElementById('orgEmail').value = clientInfo.orgEmail || '';
                document.getElementById('orgKraPin').value = clientInfo.orgKraPin || '';
            }
        }
    }

    // Event listeners
    document.getElementById('userType2').addEventListener('change', () => {
        toggleFields();
        checkFormCompletion();
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadClientInfo();
        checkFormCompletion();
    });

    const nextBtn = document.getElementById('nextBtn');
    nextBtn.addEventListener('click', () => {
        saveClientInfo();
        window.location.href = 'data_request.php';
    });

    document.getElementById('backBtn').addEventListener('click', () => {
        window.location.href = 'taxagent.php';
    });

    // Auto-check required fields
    document.querySelectorAll('input[required]').forEach(field => {
        field.addEventListener('input', checkFormCompletion);
    });




    function validateEmail(email) {
        const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return regex.test(email);
    }

    document.getElementById('email').addEventListener('blur', function() {
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

    document.getElementById('phone').addEventListener('blur', function() {
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

    function validateEmail(email) {
        const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return regex.test(email);
    }

    document.getElementById('orgEmail').addEventListener('blur', function() {
        const email = this.value;
        const emailHelp = document.getElementById('emailHelporg');

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
        const phoneHelp = document.getElementById('phoneHelporg');

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
    document.getElementById('verifyPinBtn').addEventListener('click', function() {
        const kraPin = document.getElementById('kra_pin').value;
        const kraPinError = document.getElementById('kraPinError');

        // Validate KRA PIN (basic check for empty input)
        if (!kraPin) {
            kraPinError.innerText = "Please enter a KRA PIN.";
            return;
        }

        // Send KRA PIN to the server for validation
        fetch('validate_kra_pin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'kra_pin=' + encodeURIComponent(kraPin)
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
                    document.getElementById('otherFieldsorg').style.display = 'block'; // Show other fields

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
</script>


<?php include 'footer.php'; ?>