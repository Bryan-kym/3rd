<?php 
ob_start(); // Start output buffering
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth.php';
include 'header.php'; 


try {
    $userId = authenticate();
} catch (Exception $e) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>

<!-- Add jsPDF script right after opening body -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    // Make jsPDF available globally
    window.jsPDF = window.jspdf.jsPDF;
</script>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <div class="text-center">
                        <img src="assets/images/kralogo1.png" alt="KRA Logo" class="mb-3" style="height: 50px;">
                        <h3 class="card-title text-primary fw-bold mb-1">Non-Disclosure Agreement</h3>
                        <p class="text-muted">Please carefully review and accept the terms below to proceed</p>
                    </div>
                </div>
                
                <div class="card-body px-4 px-md-5 py-4">
                    <!-- NDA Content Box -->
                    <div class="nda-content mb-4">
                        <div class="nda-header text-center mb-4">
                            <h4 class="fw-bold text-primary">CONFIDENTIALITY AGREEMENT</h4>
                            <p class="text-muted">Between Kenya Revenue Authority and the User</p>
                            <hr class="mx-auto" style="width: 100px; border-top: 2px solid #0d6efd;">
                        </div>
                        
                        <div class="nda-body">
                            <p>This Non-Disclosure Agreement ("Agreement") is entered into by and between <strong>Kenya Revenue Authority</strong> ("Disclosing Party") and <strong>You</strong> ("Receiving Party") for the purpose of preventing the unauthorized disclosure of Confidential Information as defined below.</p>
                            
                            <h5 class="mt-4 fw-bold">1. Definition of Confidential Information</h5>
                            <p>For purposes of this Agreement, "Confidential Information" shall include all information or material that has or could have commercial value or other utility in the business in which Disclosing Party is engaged.</p>
                            
                            <h5 class="mt-4 fw-bold">2. Obligations of Receiving Party</h5>
                            <p>Receiving Party shall hold and maintain the Confidential Information in strictest confidence for the sole and exclusive benefit of the Disclosing Party. Receiving Party shall carefully restrict access to Confidential Information to employees, contractors and third parties as is reasonably required.</p>
                            
                            <h5 class="mt-4 fw-bold">3. Time Periods</h5>
                            <p>The nondisclosure provisions of this Agreement shall survive the termination of this Agreement and Receiving Party's duty to hold Confidential Information in confidence shall remain in effect until the Confidential Information no longer qualifies as a trade secret or until Disclosing Party sends Receiving Party written notice releasing Receiving Party from this Agreement.</p>
                            
                            <h5 class="mt-4 fw-bold">4. Governing Law</h5>
                            <p>This Agreement shall be governed by and construed in accordance with the laws of the Republic of Kenya. Any disputes arising under this Agreement shall be resolved in the appropriate courts of Kenya.</p>
                            
                            <div class="signature-notice mt-4 p-3 bg-light rounded">
                                <p class="mb-0"><strong>By typing your full name below and checking the agreement box, you acknowledge that you have read, understood, and agree to be legally bound by all terms and conditions of this Non-Disclosure Agreement.</strong></p>
                            </div>
                        </div>
                    </div>

                    <!-- Agreement Form -->
                    <form id="ndaForm" class="needs-validation" novalidate>
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="signature" placeholder="John Doe" required>
                            <label for="signature">Full Name (Electronic Signature)</label>
                            <div class="invalid-feedback">Please enter your full name as electronic signature</div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agreement" required>
                            <label class="form-check-label fw-bold" for="agreement">
                                I acknowledge that I have read and agree to all terms of this Non-Disclosure Agreement
                            </label>
                            <div class="invalid-feedback">You must agree to the terms to proceed</div>
                        </div>
                        
                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-4 pt-2">
                            <button type="button" id="backBtn" class="btn btn-outline-secondary px-4 py-2">
                                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                            </button>
                            <button type="button" id="nextBtn" class="btn btn-primary px-4 py-2" disabled>
                                Accept and Continue <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom NDA Styling */
    .nda-content {
        max-height: 400px;
        overflow-y: auto;
        padding: 1.5rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background-color: #fdfdfd;
    }
    
    .nda-content::-webkit-scrollbar {
        width: 8px;
    }
    
    .nda-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .nda-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .nda-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    .nda-body h5 {
        color: #2c3e50;
        font-size: 1.1rem;
    }
    
    .nda-body p {
        color: #4a5568;
        line-height: 1.6;
    }
    
    .signature-notice {
        border-left: 4px solid #0d6efd;
    }
    
    /* Card Styling */
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }
    
    /* Form Styling */
    .form-floating label {
        color: #6c757d;
    }
    
    .form-control {
        border: 1px solid #ced4da;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    /* Button Styling */
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    
    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .nda-content {
            max-height: 300px;
            padding: 1rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const signatureInput = document.getElementById('signature');
    const agreementCheckbox = document.getElementById('agreement');
    const nextBtn = document.getElementById('nextBtn');
    const ndaForm = document.getElementById('ndaForm');
    
    // Form validation function
    function validateForm() {
        const isSignatureValid = signatureInput.value.trim().length > 0;
        const isAgreementChecked = agreementCheckbox.checked;
        nextBtn.disabled = !(isSignatureValid && isAgreementChecked);
    }
    
    // Event listeners for form validation
    signatureInput.addEventListener('input', validateForm);
    agreementCheckbox.addEventListener('change', validateForm);
    
    // Back button functionality
    document.getElementById('backBtn').addEventListener('click', function() {
        window.location.href = 'dashboard.php';
    });
    
    // Next button functionality - handles PDF generation and submission
    nextBtn.addEventListener('click', async function() {
        if (nextBtn.disabled) return;
        
        if (typeof jsPDF === 'undefined') {
            alert('PDF library not loaded. Please refresh the page.');
            return;
        }

        const name = signatureInput.value.trim();
        const token = localStorage.getItem('authToken');

        try {
            // 1. Fetch the Base64 image
            const imageResponse = await fetch('image_encode.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            
            if (!imageResponse.ok) {
                if (imageResponse.status === 401) {
                    window.location.href = 'login.php?session_expired=1';
                    return;
                }
                throw new Error('Failed to fetch image');
            }
            
            const imageData = await imageResponse.text();

            // 2. Create PDF document
            const doc = new jsPDF();
            
            // Add CONFIDENTIAL tag
            doc.setFont("Georgia", "bold");
            doc.setFontSize(10);
            doc.text("PUBLIC", doc.internal.pageSize.width - 10, 10, { align: "right" });

            // Add date
            const today = new Date();
            const formattedDate = today.toLocaleDateString('en-GB');

            // Add KRA logo
            const imgWidth = 90;
            const imgHeight = 23;
            const pageWidth = doc.internal.pageSize.width;
            const xPos = (pageWidth - imgWidth) / 2;
            doc.addImage(imageData, 'PNG', xPos, 12, imgWidth, imgHeight);

            // Add NDA content
            doc.setFont("Arial", "normal");
            doc.setFontSize(12);
            
            const ndaContent = [
                { text: "Terms of the Agreement", y: 50 },
                { text: `This Non-Disclosure Agreement is entered into on ${formattedDate}`, y: 60 },
                { text: "by and between:", y: 70 },
                { text: "1. Kenya Revenue Authority (KRA), a State Corporation in the Republic of Kenya,", y: 80 },
                { text: "duly incorporated under the Kenya Revenue Authority Act (Cap. 469) of the Laws of Kenya", y: 90 },
                { text: "2. " + name + " (Hereinafter referred to as the \"Receiving Party\")", y: 100 }
            ];

            ndaContent.forEach(item => {
                doc.text(item.text, 10, item.y);
            });

            // Add footer
            doc.setFont("Georgia", "bold");
            doc.setFontSize(16);
            doc.setTextColor(255, 0, 0);
            doc.text("Tulipe Ushuru, Tijitegemee!", 80, 288);

            // 3. Save PDF to server
            const pdfData = doc.output('datauristring').split(',')[1];
            
            const response = await fetch('save_pdf.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    pdf: pdfData,
                    name: name
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Failed to save PDF');
            }
            
            if (data.success) {
                // 4. Store NDA completion status and redirect
                localStorage.setItem('nda_form', JSON.stringify({
                    filePath: data.filePath,
                    formData: data.nda_form
                }));
                window.location.href = 'options.php';
            } else {
                throw new Error(data.message || 'Server error');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        }
    });
});
</script>

<?php 
include 'footer.php';
ob_flush(); // Flush the output buffer
?>