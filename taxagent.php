<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Tax Agent Information</h3>
            <p>Please fill in your information below.</p>

            <!-- Personal Information Form -->
            <form id="step3Form">
                <!-- Dropdown to select Individual or Organization -->
                <div class="form-group">
                    <label for="userType">Are you an Individual or representing an Organization? Choose from the options below</label>
                    <select class="form-control" id="userType" name="userType" required>
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

<!-- OTP Verification Modal -->
<div class="modal fade" id="otpModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify Your Email</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>An OTP has been sent to your email. Please enter it below:</p>
                <input type="text" class="form-control" id="otpInput" placeholder="Enter OTP">
                <p id="otpError" class="text-danger"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="resendOtpBtn">Resend OTP</button>
                <button type="button" class="btn btn-primary" id="verifyOtpBtn">Verify OTP</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
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








        let generatedOtp = ''; // Store generated OTP

        function generateOtp() {
            return Math.floor(100000 + Math.random() * 900000); // Generate a 6-digit OTP
        }

        function checkFormCompletion() {
            const userType = document.getElementById('userType').value;
            let requiredFields;

            if (userType === 'organization') {
                requiredFields = document.querySelectorAll('#organizationFields input[required]');
            } else {
                requiredFields = document.querySelectorAll('#individualFields input[required]');
            }

            const allFilled = Array.from(requiredFields).every(field => field.value.trim() !== '');
            document.getElementById('nextBtn').disabled = !allFilled;
        }

        function saveFormData() {
            const formData = {
                userType: document.getElementById('userType').value,
                surname: document.getElementById('surname').value,
                othernames: document.getElementById('othernames').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                kra_pin: document.getElementById('kra_pin').value,
                orgName: document.getElementById('orgName').value,
                orgPhone: document.getElementById('orgPhone').value,
                orgEmail: document.getElementById('orgEmail').value,
                orgKraPin: document.getElementById('orgKraPin').value
            };
            localStorage.setItem('taxAgentInfo', JSON.stringify(formData));
        }

        function loadFormData() {
            const storedData = localStorage.getItem('taxAgentInfo');
            if (storedData) {
                const formData = JSON.parse(storedData);
                document.getElementById('userType').value = formData.userType;
                document.getElementById('surname').value = formData.surname;
                document.getElementById('othernames').value = formData.othernames;
                document.getElementById('email').value = formData.email;
                document.getElementById('phone').value = formData.phone;
                document.getElementById('kra_pin').value = formData.kra_pin;
                document.getElementById('orgName').value = formData.orgName;
                document.getElementById('orgPhone').value = formData.orgPhone;
                document.getElementById('orgEmail').value = formData.orgEmail;
                document.getElementById('orgKraPin').value = formData.orgKraPin;

                // Show/hide the correct section based on the userType
                if (formData.userType === 'organization') {
                    document.getElementById('individualFields').style.display = 'none';
                    document.getElementById('organizationFields').style.display = 'block';
                } else {
                    document.getElementById('individualFields').style.display = 'block';
                    document.getElementById('organizationFields').style.display = 'none';
                }
                checkFormCompletion();
            }
        }

        // Call the function to load data when the page loads
        loadFormData();

        document.getElementById('userType').addEventListener('change', function() {
            if (this.value === 'organization') {
                document.getElementById('individualFields').style.display = 'none';
                document.getElementById('organizationFields').style.display = 'block';
            } else {
                document.getElementById('individualFields').style.display = 'block';
                document.getElementById('organizationFields').style.display = 'none';
            }
            checkFormCompletion();
        });

        document.querySelectorAll('#step3Form input').forEach(input => {
            input.addEventListener('input', checkFormCompletion);
        });

        document.getElementById('nextBtn').addEventListener('click', function() {
            saveFormData(); // Ensure data is saved before proceeding

            let email = document.getElementById('email').value;
            fetch('send_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        $('#otpModal').modal('show'); // Open OTP modal
                    } else {
                        alert("Error sending OTP. Try again.");
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        document.getElementById('verifyOtpBtn').addEventListener('click', function() {
            let email = document.getElementById('email').value;
            let otp = document.getElementById('otpInput').value;

            fetch('verify_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'email=' + encodeURIComponent(email) + '&otp=' + encodeURIComponent(otp)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        $('#otpModal').modal('hide'); // Close OTP modal

                        window.location.href = 'client.php';

                    } else {
                        document.getElementById('otpError').innerText = data.message;
                    }
                })
                .catch(error => console.error('Error:', error));
        });


        document.getElementById('resendOtpBtn').addEventListener('click', function() {
            let email = document.getElementById('email').value;

            fetch('send_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert("A new OTP has been sent.");
                    } else {
                        alert("Error resending OTP. Try again.");
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        document.getElementById('backBtn').addEventListener('click', function() {
            saveFormData();
            window.location.href = 'options.php';
        });
    });
</script>

<?php include 'footer.php'; ?>