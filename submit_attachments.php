<?php
// Include database connection and configuration
include 'config.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve hidden form data
    $category = $_POST['category'];
    $dataDescription = $_POST['dataDescription'];
    $specificFields = $_POST['specificFields'];
    $dateFrom = $_POST['dateFrom'];
    $dateTo = $_POST['dateTo'];
    $requestReason = $_POST['requestReason'];

    // Retrieve personal information
    $surname = $_POST['surname'];
    $otherNames = $_POST['othernames'];
    $names = $surname . ' ' . $otherNames; // Combine names
    $email = $_POST['email'];
    $phoneNumber = $_POST['phone'];
    $kraPin = $_POST['kra_pin'];
    $org_name = $_POST['orgName'];
    $orgEmail = $_POST['orgEmail'];
    $orgPhone = $_POST['orgPhone'];
    $orgKraPin = $_POST['orgKraPin'];
    $inst_name = $_POST['inst_name'];
    $inst_email = $_POST['inst_email'];
    $inst_phone = $_POST['inst_phone'];
    $other_desc = $_POST['other_description'];
    $taxagent_type = $_POST['taxagent_type'];
    $taxagent_type2 = $_POST['taxagent_type2'];
    $taxagent_name2 = $_POST['taxagent_name2'];


    if ($category === 'privatecompany' | $category === 'publiccompany') {
        $affiliation_name = $org_name;
        $affiliation_email = $orgEmail;
        $affiliation_phone = $orgPhone;
        $affiliation_kraPin = $orgKraPin;
    } else if ($category === 'student' | $category === 'researcher') {
        $affiliation_name = $inst_name;
        $affiliation_email = $inst_email;
        $affiliation_phone = $inst_phone;
        $affiliation_kraPin = '';
    } else if ($category === 'taxagent') {
        $affiliation_name = $org_name . ' ' . $taxagent_name2; // Combine names
        $affiliation_email = $orgEmail;
        $affiliation_phone = $orgPhone;
        $affiliation_kraPin = $orgKraPin;
        $client_type = $taxagent_type2;
    }


    // Insert personal information into requestors table
    $stmt = $conn->prepare("INSERT INTO requestors (fullnames, phone_number, email, requester_type, 
    kra_pin, requester_affiliation_name, requester_affiliation_phone, 
    requester_affiliation_email, requester_affiliation_pin, other_desc, taxagent_type, client_type) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "sissssisssss",
        $names,
        $phoneNumber,
        $email,
        $category,
        $kraPin,
        $affiliation_name,
        $affiliation_phone,
        $affiliation_email,
        $affiliation_kraPin,
        $other_desc,
        $taxagent_type,
        $client_type
    );

    if (!$stmt->execute()) {
        echo "Error saving personal information: " . $stmt->error;
        exit; // Stop execution if there is an error
    }

    // Get the ID of the last inserted personal info record
    $personalInfoId = $stmt->insert_id;

    // Insert request details into requests table
    $stmt2 = $conn->prepare("INSERT INTO requests (requested_by, description, specific_fields, period_from, period_to, request_purpose) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("isssss", $personalInfoId, $dataDescription, $specificFields, $dateFrom, $dateTo, $requestReason);

    if (!$stmt2->execute()) {
        echo "Error saving request details: " . $stmt2->error;
        exit; // Stop execution if there is an error
    }

    // Get the ID of the last inserted request record
    $requestId = $stmt2->insert_id;


    // Handle file uploads
    $uploadedFiles = []; // Array to hold file paths

    foreach ($_FILES as $inputName => $file) {
        if ($file['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/'; // Directory for uploads
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); // Get file extension
            $fileType = $file['type']; // Get MIME type


            // Determine the document type based on the input name or ID
            switch ($inputName) {
                case 'nacostiPermit':
                    $docType = 'NACOSTI Permit (Student)';
                    break;
                case 'introductionLetter':
                    $docType = 'Letter of Introduction from instituition (Student)';
                    break;
                case 'nacostiPermitResearcher':
                    $docType = 'NACOSTI Permit (Researcher)';
                    break;
                case 'introductionLetterResearcher':
                    $docType = 'Letter of introduction from instituition (Researcher)';
                    break;
                case 'idPassport':
                    $docType = 'ID/Passport';
                    break;
                case 'pinCertificate':
                    $docType = 'KRA pin certificate';
                    break;
                case 'consentLetter':
                    $docType = 'Constent letter from client';
                    break;
                case 'requestLetter':
                    $docType = 'Request letter from authorized signatory';
                    break;
                case 'requestLetterSignatoryOne':
                    $docType = 'Request letter from two authorized signatories';
                    break;
            }

            // Rename the file based on personalInfoId and surname
            $newFileName = $personalInfoId . '_' . $surname . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName; // New file path

            // Move the uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $uploadedFiles[] = $uploadFile; // Add file path to array

                // Insert file paths into attachments table
                $stmt3 = $conn->prepare("INSERT INTO requestors_documents (request_id, requester_id, document_file_path, document_type, document_name) 
                VALUES (?, ?, ?, ?,?)");
                $stmt3->bind_param("iisss", $requestId, $personalInfoId, $uploadFile, $fileType, $docType);
                if (!$stmt3->execute()) {
                    echo "Error saving attachment: " . $stmt3->error;
                }
            } else {
                echo "Error uploading file: " . $file['name'];
            }
        } else {
            echo "Error with file: " . $file['name'] . " - Error Code: " . $file['error'];
        }
    }


    // Redirect to a success page or confirmation
    header("Location: index.php");
    exit();
}

$conn->close(); // Close database connection
