<div class="page card rounded 0">
        <div class="card-header" onclick="hideFunction()">
            <h5>Compliance with Data Protection</h5>
        </div>
        <div class="card-body" id="persInfo">
            <div class="row">
                <div class="col-12">
                    <div id="terms" class="scrollable-div" name="t$c"></div>
                </div>
                <div class="col-12">
                    <div class="form-check" id="Terms_check" hidden>
                        <input class="form-check-input" type="checkbox" id="flexCheckChecked" required>
                        <label class="form-check-label fw-bold" for="flexCheckChecked">
                            I agree to the privacy statement
                        </label>
                    </div>
                    <p class="fw-light fst-italic fw-bold" id="condition_check">Scroll to the bottom to proceed</p>
                </div>
            </div>
        </div>
        <div class="row mb-3 mr-2 ml-2">
            <div class="col-12">
                <label for="userName" class="form-label fw-bold">Your Name:</label>
                <input type="text" class="form-control" id="userName" name="userName" placeholder="Enter your name" required>
            </div>
        </div>
        <div class="row ">
            <div class="col-6 d-flex justify-content-end">
                <button class="btn btn-primary next-button" id="next-button" disabled>Next</button>
            </div>
        </div>
        <p class="fst-italic "><b class="fw-bold">Please note:</b> We do not share personal identifiable information except for your own.</p>
        <p class="fst-italic">Please include all the necessary attachments. For further clarification
            <a href="mailto:datagovernance@kra.go.ke&subject=Data Request Inquiry" class="link-style">Click here</a>
            for contact information.
        </p>
    </div>

    <script>
        let today = new Date();
        let year = today.getFullYear();
        let month = String(today.getMonth() + 1).padStart(2, '0');
        let day = String(today.getDate()).padStart(2, '0');
        let formatted_date = `${day}/${month}/${year}`;

        const termsAndConditions = `
By using our services, you agree to the following...
Last Updated: ${formatted_date}

Welcome to [Company Name]. We value your privacy and are committed to protecting your personal data. Please read this document carefully to understand how we collect, use, and protect your information.

1. Introduction
By accessing and using our website and services, you agree to comply with these Terms and Conditions, including our data privacy policies as described below. If you do not agree to these terms, please discontinue use of our services immediately.

2. Data Collection
We collect personal data to provide better services to our users. The types of data we collect may include, but are not limited to:

- Name
- Contact information (email, phone number)
- Address
- Payment details
- Usage data (such as IP address, browser type, and usage patterns)

3. Use of Collected Data
We use your personal data for the following purposes:

- To process transactions and provide the requested services
- To improve our website and services through analytics
- To send you important information regarding your account or services
- To communicate promotions, news, or other marketing material (if consented)

4. Data Security
We are committed to ensuring that your information is secure. We implement a variety of security measures to maintain the safety of your personal data. However, no method of transmission over the internet or electronic storage is 100% secure, and we cannot guarantee its absolute security.

5. Data Sharing
We do not sell, trade, or otherwise transfer your personal information to outside parties except as necessary to provide services (such as to payment processors) or if required by law.

6. Cookies
Our website uses cookies to enhance the user experience. Cookies are small files that a site or service provider transfers to your device through your web browser (if allowed) that enables the website’s systems to recognize your browser and capture and remember certain information.

You can choose to disable cookies through your browser settings, but doing so may affect the functionality of our site.

7. User Rights
You have the following rights regarding your personal data:

- Access: You can request a copy of the data we hold about you.
- Correction: You can request corrections to any inaccurate or incomplete data.
- Deletion: You can request that we delete your personal data, subject to legal retention obligations.
- Data Portability: You can request that your data be transferred to another service provider.
To exercise any of these rights, please contact us at [contact information].

8. Third-Party Links
Our website may contain links to third-party websites. We are not responsible for the privacy practices of those sites and encourage you to review their privacy policies before providing any personal information.

9. Changes to This Policy
We may update these Terms and Conditions periodically. Any changes will be posted on this page with an updated revision date. Please check back frequently to stay informed of any updates.

10. Contact Information
If you have any questions about this privacy policy, please contact us at:

[Company Name]
[Address]
[Email Address]
[Phone Number]
        `;

        // Populate the terms and conditions in the div
        document.getElementById('terms').textContent = termsAndConditions;

        const terms = document.getElementById('terms');
        const checkbox = document.getElementById('flexCheckChecked');
        const checkboxes = document.getElementById('Terms_check');
        const userNameInput = document.getElementById('userName');
        const nextButton = document.getElementById('next-button');
        const checknext = document.getElementById('condition_check');

        checkboxes.hidden = true;

        terms.addEventListener('scroll', function () {
            if (terms.scrollTop + terms.clientHeight >= terms.scrollHeight) {
                checkboxes.hidden = false;
                checknext.hidden = true;
            }
        });

        // Function to enable or disable the Next button
        function updateNextButtonState() {
            nextButton.disabled = !(checkbox.checked && userNameInput.value.trim() !== "");
        }

        checkbox.addEventListener('change', updateNextButtonState);
        userNameInput.addEventListener('input', updateNextButtonState);

        const radioButtons = document.querySelectorAll('input[name="options"]');

        nextButton.addEventListener('click', function () {
    // Check if the checkbox is checked and the name is filled
    if (checkbox.checked && userNameInput.value.trim() !== "") {
        // Redirect to step2.php
        window.location.href = 'step2.php'; // Redirect to step2.php
    } else {
        // You can also provide feedback if the conditions are not met
        alert("Please agree to the privacy statement and enter your name.");
    }
});

    </script>