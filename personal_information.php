<?php
session_start();
include 'header.php';
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Personal Information</h3>
            <p>Please fill in your personal information below.</p>

            <!-- Personal Information Form -->
            <form id="step3Form">
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

                <!-- KRA PIN (Hidden for Students) -->
                <div class="form-group" id="kraPinField">
                    <label for="kra_pin">KRA PIN</label>
                    <input type="text" class="form-control" id="kra_pin" name="kra_pin">
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

<!-- OTP Modal -->
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
    // Select required input fields and the 'Next' button
    const requiredFields = document.querySelectorAll('#step3Form input[required]');
    const nextBtn = document.getElementById('nextBtn');

    // Function to check if all required fields are filled
    function checkFormCompletion() {
        const allFilled = Array.from(requiredFields).every(field => field.value.trim() !== '');
        nextBtn.disabled = !allFilled;
    }

    // Function to save form data to localStorage
    function saveFormData() {
        const formData = {
            surname: document.getElementById('surname').value,
            othernames: document.getElementById('othernames').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            kra_pin: document.getElementById('kra_pin').value
        };
        localStorage.setItem('personalInfo', JSON.stringify(formData));
    }

    // Function to load saved form data
    function loadFormData() {
        const savedData = localStorage.getItem('personalInfo');
        if (savedData) {
            const formData = JSON.parse(savedData);
            document.getElementById('surname').value = formData.surname || '';
            document.getElementById('othernames').value = formData.othernames || '';
            document.getElementById('email').value = formData.email || '';
            document.getElementById('phone').value = formData.phone || '';
            document.getElementById('kra_pin').value = formData.kra_pin || '';

            checkFormCompletion(); // Ensure 'Next' button updates
        }
    }

    // Load data on page load
    document.addEventListener('DOMContentLoaded', function() {
        const category = localStorage.getItem('selectedCategory');
        if (category === 'student') {
            document.getElementById('kraPinField').style.display = 'none';
        }
        loadFormData(); // Load previously saved data
    });

    // Enable 'Next' button dynamically when typing
    requiredFields.forEach(field => {
        field.addEventListener('input', checkFormCompletion);
    });

    // Save form data and send OTP when 'Next' is clicked
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

    // OTP Verification
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

                    // Redirect based on category
                    const category = localStorage.getItem('selectedCategory');
                    if (category === 'student' || category === 'researcher') {
                        window.location.href = 'institution_details.php';
                    } else if (category === 'privatecompany' || category === 'publiccompany') {
                        window.location.href = 'org.php';
                    } else {
                        window.location.href = 'data_request.php';
                    }

                } else {
                    document.getElementById('otpError').innerText = data.message;
                }
            })
            .catch(error => console.error('Error:', error));
    });

    // Resend OTP
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

    // Handle 'Back' button click - Save data and go back
    document.getElementById('backBtn').addEventListener('click', function() {
        saveFormData(); // Save input before navigating back
        window.location.href = 'options.php';
    });
</script>


<?php include 'footer.php'; ?>