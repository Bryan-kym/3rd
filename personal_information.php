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
// Enable 'Next' button when required fields are filled
const requiredFields = document.querySelectorAll('#step3Form input[required]');
const nextBtn = document.getElementById('nextBtn');

requiredFields.forEach(field => {
    field.addEventListener('input', checkFormCompletion);
});

function checkFormCompletion() {
    nextBtn.disabled = !Array.from(requiredFields).every(field => field.value.trim() !== '');
}

// Hide KRA PIN if user category is "Student"
document.addEventListener('DOMContentLoaded', function() {
    const category = localStorage.getItem('selectedCategory');
    if (category === 'student') {
        document.getElementById('kraPinField').style.display = 'none';
    }
});

document.getElementById('nextBtn').addEventListener('click', function() {
    let email = document.getElementById('email').value;

    fetch('send_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            $('#otpModal').modal('show');
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
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email) + '&otp=' + encodeURIComponent(otp)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            $('#otpModal').modal('hide'); // Close OTP modal

            // Retrieve selected category from localStorage
            const category = localStorage.getItem('selectedCategory');

            // Redirect based on selected category
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
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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


// Back button
document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'options.php';
});
</script>

<?php include 'footer.php'; ?>
