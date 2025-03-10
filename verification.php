<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Verify Your Information</h3>
            <p>Please review your details before final submission.</p>

            <div id="verificationDetails">
                <h5>Personal Information</h5>
                <p><strong>Surname:</strong> <span id="v_surname"></span></p>
                <p><strong>Other Names:</strong> <span id="v_othernames"></span></p>
                <p><strong>Email:</strong> <span id="v_email"></span></p>
                <p><strong>Phone:</strong> <span id="v_phone"></span></p>
                <p><strong>KRA PIN:</strong> <span id="v_kra_pin"></span></p>

                <h5>Request Details</h5>
                <p><strong>Category:</strong> <span id="v_category"></span></p>
                <p><strong>Data Description:</strong> <span id="v_dataDescription"></span></p>
                <p><strong>Request Reason:</strong> <span id="v_requestReason"></span></p>
                <p><strong>Date Range:</strong> <span id="v_dateFrom"></span> to <span id="v_dateTo"></span></p>

                <h5>Attachments</h5>
                <div id="attachmentsList">
                    <p>Ensure all required files are uploaded correctly.</p>
                </div>
            </div>

            <button type="button" id="backBtn" class="btn btn-secondary">Edit</button>
            <button type="button" id="confirmSubmitBtn" class="btn btn-success float-right">Submit</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function getStoredData(key) {
        let data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    }

    let personalInfo = getStoredData("personalInfo");
    let dataRequestInfo = getStoredData("dataRequestInfo");
    let uploadedAttachments = getStoredData("uploadedAttachments");
    let selectedCategory = localStorage.getItem("selectedCategory") || "N/A";

    function populateField(id, value, defaultValue = "N/A") {
        let element = document.getElementById(id);
        if (element) element.textContent = value || defaultValue;
    }

    if (personalInfo) {
        populateField("v_surname", personalInfo.surname);
        populateField("v_othernames", personalInfo.othernames);
        populateField("v_email", personalInfo.email);
        populateField("v_phone", personalInfo.phone);
        populateField("v_kra_pin", personalInfo.kra_pin);
    }

    if (dataRequestInfo) {
        populateField("v_dataDescription", dataRequestInfo.dataDescription);
        populateField("v_dateFrom", dataRequestInfo.dateFrom);
        populateField("v_dateTo", dataRequestInfo.dateTo);
        populateField("v_requestReason", dataRequestInfo.requestReason);
    }

    populateField("v_category", selectedCategory);

    let attachmentsList = document.getElementById("attachmentsList");
    if (uploadedAttachments && Object.keys(uploadedAttachments).length > 0) {
        attachmentsList.innerHTML = "<ul>" + Object.keys(uploadedAttachments).map(fileName => 
            `<li><a href='uploads/${fileName}' target='_blank'>${fileName}</a></li>`
        ).join('') + "</ul>";
    } else {
        attachmentsList.innerHTML = "<p>No files uploaded.</p>";
    }

    document.getElementById("backBtn").addEventListener("click", function () {
        window.location.href = "attachments.php";
    });

    document.getElementById("confirmSubmitBtn").addEventListener("click", function () {
        let formData = new FormData();
        formData.append("personalInfo", JSON.stringify(personalInfo));
        formData.append("dataRequestInfo", JSON.stringify(dataRequestInfo));
        formData.append("uploadedAttachments", JSON.stringify(uploadedAttachments));
        formData.append("category", selectedCategory);

        fetch("submit_attachments.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert("Submission successful!");
            localStorage.clear();
            window.location.href = "index.php";
        })
        .catch(error => {
            alert("Error submitting data: " + error);
        });
    });
});
</script>

<?php include 'footer.php'; ?>