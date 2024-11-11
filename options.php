<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Select Your Category</h3>
            <p>Please choose your category from the options below.</p>

            <!-- Category Selection Form -->
            <form id="step2Form">
                <div class="row">
                    <!-- Radio Buttons Styled as Buttons -->
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="taxpayer" value="taxpayer" required>
                        <label class="btn btn-outline-primary w-100" for="taxpayer">Taxpayer</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="taxagent" value="taxagent" required>
                        <label class="btn btn-outline-primary w-100" for="taxagent">Tax Agent</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="student" value="student" required>
                        <label class="btn btn-outline-primary w-100" for="student">Student</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="researcher" value="researcher" required>
                        <label class="btn btn-outline-primary w-100" for="researcher">Researcher</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="privatecompany" value="privatecompany" required>
                        <label class="btn btn-outline-primary w-100" for="privatecompany">Private Company</label>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input class="btn-check" type="radio" name="category" id="publiccompany" value="publiccompany" required>
                        <label class="btn btn-outline-primary w-100" for="publiccompany">Public Company</label>
                    </div>
                    <!-- <div class="col-12 mb-3">
                        <input class="btn-check" type="radio" name="category" id="others" value="others" required>
                        <label class="btn btn-outline-primary w-100" for="others">Others</label>
                    </div> -->
                </div>

                <!-- Conditional Text Input for "Others" -->
                <div class="form-group mt-3" id="otherDescription" style="display: none;">
                    <label for="description">Please provide a brief description (max 200 characters):</label>
                    <input type="text" class="form-control" id="description" maxlength="200">
                </div>

                <!-- Navigation Buttons -->
                <button type="button" id="backBtn" class="btn btn-secondary mt-4">Back</button>
                <button type="button" id="nextBtn" class="btn btn-primary float-right mt-4" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

<script>
// Enable 'Next' button based on category selection and description input
document.querySelectorAll('input[name="category"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const nextBtn = document.getElementById('nextBtn');
        const otherDescription = document.getElementById('otherDescription');

        // Store selected category in localStorage
        localStorage.setItem('selectedCategory', this.value);

        if (this.value === 'others') {
            otherDescription.style.display = 'block';
            checkDescription(); // Check if description is valid
        } else {
            otherDescription.style.display = 'none';
            document.getElementById('description').value = ''; // Clear description field if hidden
            nextBtn.disabled = false; // Enable Next button for other categories
        }
    });
});

// Check if description is valid
document.getElementById('description').addEventListener('input', checkDescription);

function checkDescription() {
    const nextBtn = document.getElementById('nextBtn');
    const description = document.getElementById('description').value;

    // Enable Next button only if description is provided
    nextBtn.disabled = description.trim() === '' || description.length > 200;
}

// Handle navigation to the next or previous steps
document.getElementById('nextBtn').addEventListener('click', function() {
    // Save the description in localStorage if the category is "Others"
    const selectedCategory = localStorage.getItem('selectedCategory');
    if (selectedCategory === 'others') {
        const description = document.getElementById('description').value;
        localStorage.setItem('description', description); // Save description if "Others" is selected
    } else {
        localStorage.removeItem('description'); // Clear description if not "Others"
    }

    // redirect to the next step
    if (selectedCategory === 'taxagent') {
        window.location.href = 'taxagent.php'; 
    }else{
        window.location.href = 'personal_information.php'; 
    }
    
});

document.getElementById('backBtn').addEventListener('click', function() {
    window.location.href = 'index.php'; // Redirect back to Step 1
});
</script>

<?php include 'footer.php'; ?>
