<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Non-Disclosure Agreement</h3>
            <p>Please read and agree to the following NDA terms to proceed.</p>
            
            <!-- NDA Text (You can customize this text as needed) -->
            <div class="mb-4" style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                <p><strong>Non-Disclosure Agreement (NDA)</strong></p>
                <p>This Agreement is made between [Organization Name] and the user. By agreeing to this NDA, you commit not to disclose any proprietary or confidential information shared by [Organization Name] during the course of this application.</p>
                <p>Your acceptance of these terms is required to proceed with the request for information. This NDA is legally binding and will be enforceable in accordance with the laws of the applicable jurisdiction.</p>
                <p>By typing your name in the box below and clicking "I Agree," you confirm your consent to the terms outlined in this agreement.</p>
                <!-- Add more NDA content as needed -->
            </div>

            <!-- Signature Input and Agreement Checkbox -->
            <form id="ndaForm">
                <div class="form-group">
                    <label for="signature">Type your name as a signature</label>
                    <input type="text" class="form-control" id="signature" required>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="agreement" required>
                    <label class="form-check-label" for="agreement">I agree to the terms and conditions of this NDA</label>
                </div>

                <!-- Next button, disabled until the name is entered and box checked -->
                <button type="button" id="nextBtn" class="btn btn-primary float-right mt-3" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

<script>

// Enable 'Next' button when name is entered and checkbox is checked
document.getElementById('ndaForm').addEventListener('input', function() {
    const signature = document.getElementById('signature').value.trim();
    const agreement = document.getElementById('agreement').checked;
    document.getElementById('nextBtn').disabled = !(signature && agreement);
});

// AJAX call to generate PDF on 'Next' button click
document.getElementById('nextBtn').addEventListener('click', function() {
    const name = document.getElementById('signature').value.trim();

    // AJAX request to generate PDF
    fetch('generate_pdf.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `name=${encodeURIComponent(name)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.filePath) {
            console.log('PDF saved at:', data.filePath); // Path where PDF is saved
            window.location.href = 'options.php'; // Redirect to Step 2
        } else {
            throw new Error('File path not found in response');
        }
    })
    .catch(error => console.error('Error:', error));
});

</script>


<?php include 'footer.php'; ?>
