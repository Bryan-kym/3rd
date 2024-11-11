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

<script>
    // Function to toggle fields based on selected option
    function toggleFields() {
        const userType = document.getElementById('userType').value;
        const individualFields = document.getElementById('individualFields');
        const organizationFields = document.getElementById('organizationFields');

        if (userType === 'individual') {
            individualFields.style.display = 'block';
            organizationFields.style.display = 'none';
        } else {
            individualFields.style.display = 'none';
            organizationFields.style.display = 'block';
        }
    }

    // Check form completion based on selected user type
    function checkFormCompletion() {
        const userType = document.getElementById('userType').value;
        const requiredFields = userType === 'individual' ?
            document.querySelectorAll('#individualFields input[required]') :
            document.querySelectorAll('#organizationFields input[required]');

        // Enable "Next" button if all required fields in the selected section are filled
        nextBtn.disabled = !Array.from(requiredFields).every(field => field.value.trim() !== '');
    }

    // Event listeners for fields and user type selection
    document.getElementById('userType').addEventListener('change', function() {
        localStorage.setItem('userType', this.value);
        toggleFields();
        checkFormCompletion(); // Check form completion when user type changes
    });

    document.addEventListener('DOMContentLoaded', function() {
        const userTypeSelect = document.getElementById('userType');
        const savedUserType = localStorage.getItem('userType') || 'individual';
        userTypeSelect.value = savedUserType;
        toggleFields();
        checkFormCompletion();
    });

    // Add event listeners to required fields in both sections
    const individualFields = document.querySelectorAll('#individualFields input[required]');
    const organizationFields = document.querySelectorAll('#organizationFields input[required]');
    const nextBtn = document.getElementById('nextBtn');

    individualFields.forEach(field => field.addEventListener('input', checkFormCompletion));
    organizationFields.forEach(field => field.addEventListener('input', checkFormCompletion));

    // Handle navigation to next and previous steps
document.getElementById('nextBtn').addEventListener('click', function() {
    const userTypeSelect = document.getElementById('userType'); // Get the dropdown element directly
    const userType = userTypeSelect.value; // Get the selected value

    // Store values in localStorage for persistence
    localStorage.setItem('userType', userType);
    localStorage.setItem('surname', document.getElementById('surname').value);
    localStorage.setItem('othernames', document.getElementById('othernames').value);
    localStorage.setItem('email', document.getElementById('email').value);
    localStorage.setItem('phone', document.getElementById('phone').value);
    localStorage.setItem('kra_pin', document.getElementById('kra_pin').value);

    if (userType === 'organization') {
        localStorage.setItem('surname', document.getElementById('orgName').value);
        localStorage.setItem('phone', document.getElementById('orgPhone').value);
        localStorage.setItem('email', document.getElementById('orgEmail').value);
        localStorage.setItem('kra_pin', document.getElementById('orgKraPin').value);
    }

    // Redirect to next page
    window.location.href = 'client.php';
});


    document.getElementById('backBtn').addEventListener('click', function() {
        window.location.href = 'options.php';
    });
</script>

<?php include 'footer.php'; ?>