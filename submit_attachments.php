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
    $uploadedFilePath = $_POST['uploadedFilePath'];

    // Determine the affiliation details
    if ($category === 'privatecompany' || $category === 'publiccompany') {
        $affiliation_name = $org_name;
        $affiliation_email = $orgEmail;
        $affiliation_phone = $orgPhone;
        $affiliation_kraPin = $orgKraPin;
    } elseif ($category === 'student' || $category === 'researcher') {
        $affiliation_name = $inst_name;
        $affiliation_email = $inst_email;
        $affiliation_phone = $inst_phone;
        $affiliation_kraPin = '';
    } elseif ($category === 'taxagent') {
        $affiliation_name = $org_name . ' ' . $taxagent_name2;
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
        exit;
    }

    // Get the ID of the last inserted personal info record
    $personalInfoId = $stmt->insert_id;

    // === Generate Tracking ID ===
    $year = date("y"); // Get last two digits of the current year (e.g., "25" for 2025)
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(tracking_id, '/', -2) AS UNSIGNED)) AS last_number 
                            FROM requests WHERE tracking_id LIKE 'KRA/CDO/%/$year'");

    $lastNumber = ($result && $row = $result->fetch_assoc()) ? intval($row['last_number']) : 0;
    $newTrackingNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    $trackingID = "KRA/CDO/$newTrackingNumber/$year";

    // Insert request details into requests table with tracking ID
    $stmt2 = $conn->prepare("INSERT INTO requests (requested_by, description, specific_fields, period_from, period_to, request_purpose, tracking_id, date_requested) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("isssssss", $personalInfoId, $dataDescription, $specificFields, $dateFrom, $dateTo, $requestReason, $trackingID, $timestamptoday);

    if (!$stmt2->execute()) {
        echo "Error saving request details: " . $stmt2->error;
        exit;
    }

    $requestId = $stmt2->insert_id;
    // //////////////////////////////////////////////////////////////////////////

    $stmt4 = $conn->prepare("UPDATE requestors_documents 
    SET request_id = ?, requester_id = ?, last_edited_by = ?, last_edited_on = ? 
    WHERE document_file_path = ?");
    $stmt4->bind_param("iisss", $requestId, $personalInfoId, $names, $timestamptoday, $uploadedFilePath);

    if (!$stmt4->execute()) {
        echo "Error saving request details: " . $stmt4->error;
        exit;
    }
    //////////////////////////////////////////////////////////////////////////////////////////
    // === Handle File Uploads ===
    foreach ($_FILES as $inputName => $file) {
        if ($file['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $uploadDirdb = '3rd-be/uploads/';
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // Document type mapping
            $docTypes = [
                'nacostiPermit' => 'NACOSTI Permit (Student)',
                'introductionLetter' => 'Letter of Introduction (Student)',
                'nacostiPermitResearcher' => 'NACOSTI Permit (Researcher)',
                'introductionLetterResearcher' => 'Letter of Introduction (Researcher)',
                'idPassport' => 'ID/Passport',
                'pinCertificate' => 'KRA PIN Certificate',
                'consentLetter' => 'Consent Letter from Client',
                'requestLetter' => 'Request Letter (Authorized Signatory)',
                'requestLetterSignatoryOne' => 'Request Letter (Two Signatories)'
            ];
            $docType = $docTypes[$inputName] ?? 'Unknown Document';

            // Rename and save file
            $newFileName = $personalInfoId . '_' . $surname . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;
            $uploadFiledb = $uploadDirdb . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $stmt3 = $conn->prepare("INSERT INTO requestors_documents (request_id, requester_id, document_file_path, document_type, document_name, last_edited_by, last_edited_on) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt3->bind_param("iisssss", $requestId, $personalInfoId, $uploadFiledb, $fileExtension, $docType, $names, $timestamptoday);
                if (!$stmt3->execute()) {
                    echo "Error saving attachment: " . $stmt3->error;
                }
            } else {
                echo "Error uploading file: " . $file['name'];
            }
        }
    }
    // Redirect to success page
    header("Location: index.php");
    exit();
}

$conn->close();
