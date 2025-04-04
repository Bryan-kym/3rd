<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Upload Attachments</h3>
            <p>Please upload the required files before proceeding.</p>

            <form id="attachmentsForm" enctype="multipart/form-data">
                <div id="attachmentsContainer">
                    <!-- Dynamic attachment fields will be added here -->
                </div>

                <div id="previewSection" style="display: none;">
                    <h5>File Previews</h5>
                    <ul id="filePreviewList"></ul>
                </div>

                <div id="errorMessage" class="mt-3 text-danger"></div>

                <button type="button" id="backBtn" class="btn btn-secondary">Back</button>
                <button type="submit" id="submitBtn" class="btn btn-primary float-right">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let selectedCategory = localStorage.getItem("selectedCategory") || "";
        let attachmentsContainer = document.getElementById("attachmentsContainer");
        let previewSection = document.getElementById("previewSection");
        let filePreviewList = document.getElementById("filePreviewList");
        let errorMessage = document.getElementById("errorMessage");

        const attachmentRequirements = {
            "student": ["NACOSTI Permit", "Letter of Introduction"],
            "researcher": ["NACOSTI Permit", "Letter of Introduction"],
            "taxpayer": ["ID", "PIN Certificate"],
            "taxagent": ["Approved Consent Letter"],
            "publiccompany": ["Request Letter from Authorized Signatory"],
            "privatecompany": ["Request Letter from Two Authorized Signatories"]
        };

        function populateAttachments(category) {
            attachmentsContainer.innerHTML = "";
            filePreviewList.innerHTML = "";

            if (attachmentRequirements[category]) {
                attachmentRequirements[category].forEach(req => {
                    let inputDiv = document.createElement("div");
                    inputDiv.classList.add("form-group");
                    inputDiv.innerHTML = `
                    <label>${req} (PDF, JPG, PNG)</label>
                    <input type="file" name="attachments[]" class="form-control attachmentFile" data-name="${req}" data-document-type="${req}" accept=".pdf,.jpg,.png" required>
                `;
                    attachmentsContainer.appendChild(inputDiv);
                });
            }
        }

        function handleFilePreview() {
            filePreviewList.innerHTML = "";
            let files = document.querySelectorAll(".attachmentFile");
            let hasFiles = false;

            files.forEach(fileInput => {
                let file = fileInput.files[0];
                if (file) {
                    hasFiles = true;
                    let li = document.createElement("li");
                    li.textContent = `${fileInput.dataset.name}: ${file.name}`;
                    filePreviewList.appendChild(li);
                }
            });
            previewSection.style.display = hasFiles ? "block" : "none";
        }

        attachmentsContainer.addEventListener("change", handleFilePreview);

        document.getElementById("attachmentsForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let formData = new FormData();
            let valid = true;
            let errorMessage = document.getElementById("errorMessage");
            errorMessage.textContent = ""; // Reset error message

            let personalInfo = JSON.parse(localStorage.getItem("personalInfo") || "{}");
            let dataRequestInfo = JSON.parse(localStorage.getItem("dataRequestInfo") || "{}");
            let taxagentdetails = JSON.parse(localStorage.getItem("taxAgentInfo") || "{}");
            let orgdetails = JSON.parse(localStorage.getItem("org_details") || "{}");
            let instdetails = JSON.parse(localStorage.getItem("ins_details") || "{}");
            let clientdetails = JSON.parse(localStorage.getItem("clientInfo") || "{}");
            let ndaupload = localStorage.getItem("uploadedFilePath");

            formData.append("personalInfo", JSON.stringify(personalInfo));
            formData.append("dataRequestInfo", JSON.stringify(dataRequestInfo));
            formData.append("category", selectedCategory);
            formData.append("taxagentdetails", JSON.stringify(taxagentdetails));
            formData.append("orgdetails", JSON.stringify(orgdetails));
            formData.append("instdetails", JSON.stringify(instdetails));
            formData.append("clientdetails", JSON.stringify(clientdetails));
            formData.append("ndaUpload", ndaupload);

            document.querySelectorAll(".attachmentFile").forEach(input => {
                let file = input.files[0];
                if (!file) {
                    valid = false;
                    errorMessage.textContent = "All required files must be uploaded.";
                } else {
                    let attachmentName = input.getAttribute("data-name"); // Get the required attachment name
                    let fileType = file.name.split('.').pop().toLowerCase(); // Extract file extension

                    formData.append("attachments[]", file);
                    formData.append("attachment_names[]", attachmentName); // Pass attachment name
                    formData.append("attachment_types[]", fileType); // Pass attachment type
                }
            });

            if (!valid) return;

            fetch("submit_attachments.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Submission successful!");
                        localStorage.clear();
                        window.location.href = "index.php";
                    } else {
                        errorMessage.textContent = data.error || "An unexpected error occurred.";
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    errorMessage.textContent = "An unexpected error occurred. Check the console for details.";
                });
        });


        document.getElementById("backBtn").addEventListener("click", function() {
            window.location.href = "data_request.php";
        });

        populateAttachments(selectedCategory);
    });
</script>

<?php include 'footer.php'; ?>