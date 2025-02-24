<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Non-Disclosure Agreement</h3>
            <p>Please read and agree to the following NDA terms to proceed.</p>

            <!-- NDA Text -->
            <div class="mb-4" style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                <p><strong>Non-Disclosure Agreement (NDA)</strong></p>
                <p>This Agreement is made between [Organization Name] and the user. By agreeing to this NDA, you commit not to disclose any proprietary or confidential information shared by [Organization Name] during the course of this application.</p>
                <p>Your acceptance of these terms is required to proceed with the request for information. This NDA is legally binding and will be enforceable in accordance with the laws of the applicable jurisdiction.</p>
                <p>By typing your name in the box below and clicking "I Agree," you confirm your consent to the terms outlined in this agreement.</p>
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
                <button type="button" id="nextBtn" class="btn btn-primary float-right mt-3" disabled>Next</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('ndaForm').addEventListener('input', function() {
        const signature = document.getElementById('signature').value.trim();
        const agreement = document.getElementById('agreement').checked;
        document.getElementById('nextBtn').disabled = !(signature && agreement);
    });

    document.getElementById('nextBtn').addEventListener('click', async function() {
        const name = document.getElementById('signature').value.trim();

        // Fetch the Base64 image from PHP script
        const imageResponse = await fetch('image_encode.php');
        const imageData = await imageResponse.text(); // The Base64 image strings

        // Create a new PDF
        const {
            jsPDF
        } = window.jspdf;
        const pdf = new jsPDF();

        // Add CONFIDENTIAL tag
        pdf.setFont("Georgia", "bold");
        pdf.setFontSize(10);
        pdf.setTextColor(0, 0, 0); // Red color for emphasis
        pdf.text("PUBLIC", pdf.internal.pageSize.width - 10, 10, {
            align: "right"
        });

        // Get today's date
        const today = new Date();

        // Format the date to YYYY-MM-DD
        const formattedDate = today.toISOString().split('T')[0];

        // Add image to the header (centered on the page)
        const imgWidth = 90; // Width of the image
        const imgHeight = 23; // Height of the image
        const pageWidth = pdf.internal.pageSize.width;
        const xPos = (pageWidth - imgWidth) / 2; // Center the image horizontally

        pdf.addImage(imageData, 'PNG', xPos, 12, imgWidth, imgHeight); // Adjust the Y position for the image

        // Add NDA content below the image
        pdf.setFont("Arial", "normal");
        pdf.setFontSize(12);
        pdf.setTextColor(0, 0, 0); // Black color for text
        pdf.text("Terms of the Agreement", 10, 50);
        pdf.text("This Non-Disclosure Agreement (hereinafter referred to as the “Agreement”) is entered into on " + formattedDate, 10, 60);
        pdf.text("by and between:", 10, 70);
        pdf.text("1. Kenya Revenue Authority (KRA), a State Corporation in the Republic of Kenya, duly incorporated under the ", 10, 80);
        pdf.text("Kenya Revenue Authority Act (Cap. 469) of the Laws of Kenya and whose registered office is situated at Times Tower,", 10, 90);
        pdf.text("Haile Selassie Avenue and of P.O. Box 48240 – 00100, Nairobi (hereinafter referred to as “KRA” which expression shall", 10, 100);
        pdf.text("where the context so admits include its successors and assigns) of the one part; (hereinafter referred to as the", 10, 110);
        pdf.text("(“Disclosing Party”) and", 10, 120);
        pdf.text("2. [Receiving Party's Name] with an address of [Receiving Party's Address] (hereinafter referred to as the", 10, 130);
        pdf.text("\"Receiving Party\") (collectively referred to as the “Parties”).", 10, 140);


        // Add footer
        pdf.setFont("Georgia", "bold");
        pdf.setFontSize(16);
        pdf.setTextColor(255, 0, 0); // Black color for text
        pdf.text("Tulipe Ushuru, Tijitegemee!", 80, 288);

        // Convert PDF to Base64
        const pdfData = pdf.output('datauristring').split(',')[1];

        // Send the PDF to the server
        try {
            const response = await fetch('save_pdf.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    pdf: pdfData,
                    name
                })
            });
            const data = await response.json();
            if (data.success) {
                localStorage.setItem('uploadedFilePath', data.filePath);
                // Assuming you got the JSON response in a variable called responseData:
                localStorage.setItem('nda_form', data.nda_form);
                window.location.href = 'options.php'; // Redirect to Step 2
            } else {
                throw new Error(data.message || 'Failed to save PDF');
            }
        } catch (error) {
            console.error('Error creating NDA:', error);

        }
    });
</script>


<?php include 'footer.php'; ?>