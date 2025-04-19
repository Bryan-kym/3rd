<?php 
ob_start();
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    window.jsPDF = window.jspdf.jsPDF;
</script>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <!-- Simplified Header -->
                <div class="card-header bg-white border-bottom pt-4 pb-2">
                    <div class="text-center">
                        <img src="assets/images/kralogo1.png" alt="KRA Logo" class="mb-3" style="height: 50px;">
                        <h3 class="card-title text-dark fw-bold mb-1">Non-Disclosure Agreement</h3>
                        <p class="text-muted">Please carefully review and accept the terms below</p>
                    </div>
                </div>
                
                <div class="card-body px-4 px-md-5 py-4">
                    <!-- NDA Content Box -->
                    <div class="nda-content mb-4" id="ndaContent">
                        <div class="nda-header text-center mb-4">
                            <h4 class="fw-bold text-dark">CONFIDENTIALITY AGREEMENT</h4>
                            <p class="text-muted">Between Kenya Revenue Authority and the User</p>
                            <hr class="mx-auto" style="width: 100px; border-top: 2px solid #d9232e;">
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
                                <span class="btn-text">Accept and Continue</span>
                                <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #d9232e; /* KRA Red */
        --secondary-color: #151515; /* Dark Black */
        --light-color: #ffffff; /* White */
        --grey-color: #6c757d; /* Grey */
        --dark-grey: #343a40; /* Dark Grey */
        --border-radius: 0.5rem;
        --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }
    
    /* Smooth page load animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .container {
        animation: fadeIn 0.5s ease-out;
    }
    
    /* Card styling - simplified */
    .card {
        border-radius: var(--border-radius);
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    /* NDA content styling */
    .nda-content {
        max-height: 400px;
        overflow-y: auto;
        padding: 1.5rem;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: var(--border-radius);
        background-color: #fdfdfd;
        scroll-behavior: smooth;
    }
    
    .nda-content::-webkit-scrollbar {
        width: 8px;
    }
    
    .nda-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .nda-content::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 4px;
    }
    
    .nda-content::-webkit-scrollbar-thumb:hover {
        background: #b51d27;
    }
    
    .nda-body h5 {
        color: var(--secondary-color);
        font-size: 1.1rem;
    }
    
    .nda-body p {
        color: var(--dark-grey);
        line-height: 1.6;
    }
    
    .signature-notice {
        border-left: 4px solid var(--primary-color);
        background-color: rgba(217, 35, 46, 0.05);
    }
    
    /* Form styling - simplified */
    .form-floating label {
        color: var(--grey-color);
    }
    
    .form-control {
        border: 1px solid #ced4da;
        transition: var(--transition);
        border-radius: var(--border-radius);
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(217, 35, 46, 0.1);
    }
    
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    /* Button styling - primary button keeps color */
    .btn {
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
        background-color: #b51d27;
        border-color: #a81a22;
    }
    
    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
    }
    
    /* Scroll indicator */
    .scroll-indicator {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        color: var(--primary-color);
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); }
        40% { transform: translateY(-10px) translateX(-50%); }
        60% { transform: translateY(-5px) translateX(-50%); }
    }
    
    /* Responsive adjustments */
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
// [Keep all the JavaScript from previous version unchanged]
document.addEventListener('DOMContentLoaded', function() {
    const signatureInput = document.getElementById('signature');
    const agreementCheckbox = document.getElementById('agreement');
    const nextBtn = document.getElementById('nextBtn');
    const ndaForm = document.getElementById('ndaForm');
    const ndaContent = document.getElementById('ndaContent');
    
    // Add scroll indicator initially
    const scrollIndicator = document.createElement('div');
    scrollIndicator.className = 'scroll-indicator';
    scrollIndicator.innerHTML = '<i class="fas fa-chevron-down fa-lg"></i>';
    ndaContent.appendChild(scrollIndicator);
    
    // Remove scroll indicator when user scrolls
    ndaContent.addEventListener('scroll', function() {
        if (this.scrollTop > 20) {
            scrollIndicator.style.opacity = '0';
            setTimeout(() => scrollIndicator.remove(), 300);
        }
    });
    
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
            showAlert('danger', 'PDF library not loaded. Please refresh the page.');
            return;
        }

        const name = signatureInput.value.trim();
        const token = localStorage.getItem('authToken');
        
        // Show loading state
        nextBtn.disabled = true;
        nextBtn.querySelector('.btn-text').textContent = 'Processing...';
        nextBtn.querySelector('.spinner-border').classList.remove('d-none');
        nextBtn.querySelector('.fa-arrow-right').classList.add('d-none');

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
            doc.setTextColor(150, 150, 150);
            doc.text("CONFIDENTIAL", doc.internal.pageSize.width - 10, 10, { align: "right" });

            // Add date
            const today = new Date();
            const formattedDate = today.toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            // Add KRA logo
            const imgWidth = 90;
            const imgHeight = 23;
            const pageWidth = doc.internal.pageSize.width;
            const xPos = (pageWidth - imgWidth) / 2;
            doc.addImage(imageData, 'PNG', xPos, 12, imgWidth, imgHeight);

            // Add NDA content
            doc.setFont("Arial", "normal");
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            
            const ndaContent = [
                { text: "NON-DISCLOSURE AGREEMENT", style: "bold", size: 16, y: 50, align: "center" },
                { text: `This Non-Disclosure Agreement is entered into on ${formattedDate}`, y: 60 },
                { text: "by and between:", y: 70 },
                { text: "1. Kenya Revenue Authority (KRA), a State Corporation in the Republic of Kenya,", y: 80 },
                { text: "duly incorporated under the Kenya Revenue Authority Act (Cap. 469) of the Laws of Kenya", y: 90 },
                { text: "2. " + name + " (Hereinafter referred to as the \"Receiving Party\")", y: 100 }
            ];

            ndaContent.forEach(item => {
                doc.setFont("Arial", item.style || "normal");
                doc.setFontSize(item.size || 12);
                doc.text(item.text, item.align === "center" ? 105 : 10, item.y, { align: item.align || "left" });
            });

            // Add footer
            doc.setFont("Georgia", "bold");
            doc.setFontSize(16);
            doc.setTextColor(217, 35, 46); // KRA Red
            doc.text("Tulipe Ushuru, Tijitegemee!", 105, 288, { align: "center" });

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
                // Show success with confetti
                createConfetti();
                
                // Change button to success state
                nextBtn.classList.remove('btn-primary');
                nextBtn.classList.add('btn-success');
                nextBtn.querySelector('.btn-text').textContent = 'Success!';
                nextBtn.querySelector('.spinner-border').classList.add('d-none');
                
                // Redirect after delay
                setTimeout(() => {
                    // Store NDA completion status and redirect
                    localStorage.setItem('nda_form', JSON.stringify({
                        filePath: data.filePath,
                        formData: data.nda_form
                    }));
                    window.location.href = 'options.php';
                }, 1500);
            } else {
                throw new Error(data.message || 'Server error');
            }
        } catch (error) {
            console.error('Error:', error);
            
            // Show error state
            nextBtn.classList.remove('btn-primary');
            nextBtn.classList.add('btn-danger');
            nextBtn.querySelector('.btn-text').textContent = 'Error! Try Again';
            nextBtn.querySelector('.spinner-border').classList.add('d-none');
            nextBtn.querySelector('.fa-arrow-right').classList.remove('d-none');
            
            // Reset after delay
            setTimeout(() => {
                nextBtn.classList.remove('btn-danger');
                nextBtn.classList.add('btn-primary');
                nextBtn.querySelector('.btn-text').textContent = 'Accept and Continue';
                nextBtn.disabled = false;
            }, 2000);
            
            showAlert('danger', 'Error: ' + error.message);
        }
    });
    
    // Create confetti effect
    function createConfetti() {
        const colors = ['#d9232e', '#151515', '#6c757d', '#343a40'];
        for (let i = 0; i < 100; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.width = Math.random() * 10 + 5 + 'px';
            confetti.style.height = Math.random() * 10 + 5 + 'px';
            confetti.style.animationDuration = Math.random() * 2 + 2 + 's';
            confetti.style.animationDelay = Math.random() * 2 + 's';
            document.body.appendChild(confetti);
            
            // Remove after animation
            setTimeout(() => confetti.remove(), 3000);
        }
    }
    
    // Show alert message
    function showAlert(type, message) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `
            <i class="fas ${type === 'danger' ? 'fa-exclamation-circle' : 'fa-check-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add animation
        alert.style.animation = 'slideInDown 0.5s ease-out';
        
        // Insert after card header
        document.querySelector('.card-body').prepend(alert);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.5s ease-out';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    }
});
</script>

<?php 
include 'footer.php';
ob_flush();
?>