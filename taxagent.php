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
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="form-group" id="kraPinField">
                        <label for="kra_pin">KRA PIN</label>
                        <input type="text" class="form-control" id="kra_pin" name="kra_pin" required>
                    </div>
                </div>

                <!-- Fields for Organization (Hidden initially) -->
                <div id="organizationFields" style="display: none;">
                    <div class="form-group">
                        <label for="orgName">Organization Name</label>
                        <input type="text" class="form-control" id="orgName" name="orgName" required>
                    </div>
                    <div class="form-group">
                        <label for="orgPhone">Organization Phone Number</label>
                        <input type="tel" class="form-control" id="orgPhone" name="orgPhone" required>
                    </div>
                    <div class="form-group">
                        <label for="orgEmail">Organization Email</label>
                        <input type="email" class="form-control" id="orgEmail" name="orgEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="orgKraPin">Organization KRA PIN</label>
                        <input type="text" class="form-control" id="orgKraPin" name="orgKraPin" required>
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