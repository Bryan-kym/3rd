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
        let clientInfo = { userType };

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
</script>


<?php include 'footer.php'; ?>