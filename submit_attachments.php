<?php
include 'config.php'; // Your database connection details
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    try {
        // Get the raw request data
        $requestData = json_decode($_POST['requestData'] ?? '{}', true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid request data format');
        }

        // Extract data with null checks
        $personalInfo = $requestData['personalInfo'] ?? [];
        $dataRequestInfo = $requestData['dataRequestInfo'] ?? [];
        $ndaUpload = $requestData['ndaForm'] ?? null;
        $category = sanitize($requestData['category'] ?? '');
        $clientDetails = $requestData['clientInfo'] ?? [];
        $instDetails = $requestData['instDetails'] ?? [];
        $orgDetails = $requestData['orgDetails'] ?? [];
        $taxAgentDetails = $requestData['taxAgentInfo'] ?? [];

        // Initialize default values
        $surname = '';
        $otherNames = '';
        $names = '';
        $email = '';
        $phoneNumber = '';
        $kraPin = '';
        $cnames = '';
        $cemail = '';
        $cphoneNumber = '';
        $ckraPin = '';
        $clientType = '';
        $taxAgentType = '';

        // Process based on category
        switch ($category) {
            case 'taxpayer':
                $surname = sanitize($personalInfo['surname'] ?? '');
                $otherNames = sanitize($personalInfo['othernames'] ?? '');
                $names = trim("$surname $otherNames");
                $email = filter_var($personalInfo['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                $phoneNumber = preg_replace('/[^0-9]/', '', $personalInfo['phone'] ?? '');
                $kraPin = sanitize($personalInfo['kra_pin'] ?? '');
                break;

            case 'taxagent':
                $taxAgentType = sanitize($taxAgentDetails['userType'] ?? '');
                if ($taxAgentType == 'individual') {
                    $surname = sanitize($taxAgentDetails['surname'] ?? '');
                    $otherNames = sanitize($taxAgentDetails['othernames'] ?? '');
                    $names = trim("$surname $otherNames");
                    $email = filter_var($taxAgentDetails['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                    $phoneNumber = preg_replace('/[^0-9]/', '', $taxAgentDetails['phone'] ?? '');
                    $kraPin = sanitize($taxAgentDetails['kra_pin'] ?? '');
                } else {
                    $names = sanitize($taxAgentDetails['orgName'] ?? '');
                    $email = filter_var($taxAgentDetails['orgEmail'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                    $phoneNumber = preg_replace('/[^0-9]/', '', $taxAgentDetails['orgPhone'] ?? '');
                    $kraPin = sanitize($taxAgentDetails['orgKraPin'] ?? '');
                }

                $clientType = sanitize($clientDetails['userType'] ?? '');
                if ($clientType == 'individual') {
                    $surname = sanitize($clientDetails['surname'] ?? '');
                    $otherNames = sanitize($clientDetails['othernames'] ?? '');
                    $cnames = trim("$surname $otherNames");
                    $cemail = filter_var($clientDetails['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                    $cphoneNumber = preg_replace('/[^0-9]/', '', $clientDetails['phone'] ?? '');
                    $ckraPin = sanitize($clientDetails['kra_pin'] ?? '');
                } else {
                    $cnames = sanitize($clientDetails['orgName'] ?? '');
                    $cemail = filter_var($clientDetails['orgEmail'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                    $cphoneNumber = preg_replace('/[^0-9]/', '', $clientDetails['orgPhone'] ?? '');
                    $ckraPin = sanitize($clientDetails['orgKraPin'] ?? '');
                }
                break;

            case 'student':
            case 'researcher':
                $surname = sanitize($personalInfo['surname'] ?? '');
                $otherNames = sanitize($personalInfo['othernames'] ?? '');
                $names = trim("$surname $otherNames");
                $email = filter_var($personalInfo['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                $phoneNumber = preg_replace('/[^0-9]/', '', $personalInfo['phone'] ?? '');
                $kraPin = sanitize($personalInfo['kra_pin'] ?? '');
                $cnames = sanitize($instDetails['inst_name'] ?? '');
                $cemail = filter_var($instDetails['inst_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                $cphoneNumber = preg_replace('/[^0-9]/', '', $instDetails['inst_phone'] ?? '');
                break;

            case 'privatecompany':
            case 'publiccompany':
                $surname = sanitize($personalInfo['surname'] ?? '');
                $otherNames = sanitize($personalInfo['othernames'] ?? '');
                $names = trim("$surname $otherNames");
                $email = filter_var($personalInfo['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                $phoneNumber = preg_replace('/[^0-9]/', '', $personalInfo['phone'] ?? '');
                $kraPin = sanitize($personalInfo['kra_pin'] ?? '');
                $cnames = sanitize($orgDetails['orgName'] ?? '');
                $cemail = filter_var($orgDetails['orgEmail'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
                $cphoneNumber = preg_replace('/[^0-9]/', '', $orgDetails['orgPhone'] ?? '');
                $ckraPin = sanitize($orgDetails['orgKraPin'] ?? '');
                break;

            default:
                throw new Exception('Unknown request category');
        }

        $dataDescription = sanitize($dataRequestInfo['dataDescription'] ?? '');
        $specificFields = sanitize($dataRequestInfo['specificFields'] ?? '');
        $dateFrom = !empty($dataRequestInfo['dateFrom']) ? date('Y-m-d', strtotime($dataRequestInfo['dateFrom'])) : null;
        $dateTo = !empty($dataRequestInfo['dateTo']) ? date('Y-m-d', strtotime($dataRequestInfo['dateTo'])) : null;
        $requestReason = sanitize($dataRequestInfo['requestReason'] ?? '');
        $templateUpload = sanitize($dataRequestInfo['templatePath'] ?? '');
        $templateFileType = sanitize($dataRequestInfo['templateFileType'] ?? '');

        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedFiles = [];
        $attachmentNames = $_POST['attachmentNames'] ?? [];
        $attachmentTypes = [];

        // Process uploaded files
        if (!empty($_FILES['attachments'])) {
            foreach ($_FILES['attachments']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['attachments']['error'][$index] === UPLOAD_ERR_OK) {
                    $fileName = basename($_FILES['attachments']['name'][$index]);
                    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                    $uniqueFileName = time() . "_" . $fileName;
                    $filePath = $uploadDir . $uniqueFileName;

                    if (move_uploaded_file($tmpName, $filePath)) {
                        $uploadedFiles[] = [
                            "path" => $filePath,
                            "name" => $attachmentNames[$index] ?? 'Document ' . ($index + 1),
                            "type" => $fileType
                        ];
                    }
                }
            }
        }

        $conn->begin_transaction();

        try {
            // Insert requestor information
            $stmt = $conn->prepare("INSERT INTO requestors (fullnames, phone_number, email, requester_type, kra_pin, taxagent_type, client_type, 
                requester_affiliation_name, requester_affiliation_phone, requester_affiliation_email, requester_affiliation_pin) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissssssiss", $names, $phoneNumber, $email, $category, $kraPin, $taxAgentType, $clientType, $cnames, $cphoneNumber, $cemail, $ckraPin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting requestor: " . $stmt->error);
            }
            $personalInfoId = $stmt->insert_id;

            // Generate tracking ID
            $year = date("y");
            $result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(tracking_id, '/', -2) AS UNSIGNED)) AS last_number 
                                    FROM requests WHERE tracking_id LIKE 'KRA/CDO/%/$year'");
            $lastNumber = ($result && $row = $result->fetch_assoc()) ? intval($row['last_number']) : 0;
            $newTrackingNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $trackingID = "KRA/CDO/$newTrackingNumber/$year";

            // Insert request
            $stmt2 = $conn->prepare("INSERT INTO requests (tracking_id, requested_by, description, specific_fields, period_from, period_to, request_purpose, date_requested, date_requested_dt) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt2->bind_param("sisssss", $trackingID, $personalInfoId, $dataDescription, $specificFields, $dateFrom, $dateTo, $requestReason);
            
            if (!$stmt2->execute()) {
                throw new Exception("Error inserting request: " . $stmt2->error);
            }
            $requestId = $stmt2->insert_id;

            // Process uploaded files
            foreach ($uploadedFiles as $file) {
                $stmt3 = $conn->prepare("INSERT INTO requestors_documents (request_id, requester_id, document_file_path, last_edited_on, document_name, document_type) 
                                        VALUES (?, ?, ?, NOW(), ?, ?)");
                $stmt3->bind_param("iisss", $requestId, $personalInfoId, $file['path'], $file['name'], $file['type']);
                
                if (!$stmt3->execute()) {
                    throw new Exception("Error inserting document record: " . $stmt3->error);
                }
            }

            // Process NDA if exists
            if ($ndaUpload) {
                $stmt4 = $conn->prepare("UPDATE requestors_documents SET request_id = ?, requester_id = ? WHERE document_file_path = ?");
                $stmt4->bind_param("iis", $requestId, $personalInfoId, $ndaUpload);
                
                if (!$stmt4->execute()) {
                    throw new Exception("Error updating NDA: " . $stmt4->error);
                }
            }

            // Process template if exists
            if ($templateUpload) {
                $stmt5 = $conn->prepare("INSERT INTO requestors_documents (document_name, document_type, document_file_path, request_id, requester_id, last_edited_on) 
                                       VALUES ('Supporting document', ?, ?, ?, ?, NOW())");
                $stmt5->bind_param("ssii", $templateFileType, $templateUpload, $requestId, $personalInfoId);
                
                if (!$stmt5->execute()) {
                    throw new Exception("Error inserting template: " . $stmt5->error);
                }
            }

            $conn->commit(); 
            echo json_encode(["success" => true, "message" => "Data submitted successfully", "tracking_id" => $trackingID]);
             
            // Send emails
            include 'mail_requestor.php';
            include 'send_email.php';
            include 'mail_reviewers.php';
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Database error: " . $e->getMessage());
            echo json_encode(["success" => false, "error" => "An error occurred while processing your request. " . $e->getMessage()]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method."]);
}

$conn->close();