<?php
include 'config.php'; // Your database connection details
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //For debugging; remove for production

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function sanitize($data)
    {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    $upldFilePth = $_SERVER['DOCUMENT_COMP_PATH'];
    $personalInfo = json_decode($_POST['personalInfo'], true);
    $dataRequestInfo = json_decode($_POST['dataRequestInfo'], true);
    $ndaUpload = $_POST['ndaUpload'];
    $category = sanitize($_POST['category'] ?? '');

    $surname = sanitize($personalInfo['surname'] ?? '');
    $otherNames = sanitize($personalInfo['othernames'] ?? '');
    $names = trim("$surname $otherNames");
    $email = filter_var($personalInfo['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
    $phoneNumber = preg_replace('/[^0-9]/', '', $personalInfo['phone'] ?? '');
    $kraPin = isset($personalInfo['kra_pin']) ? (int)$personalInfo['kra_pin'] : null;


    $dataDescription = sanitize($dataRequestInfo['dataDescription'] ?? '');
    $specificFields = sanitize($dataRequestInfo['specificFields'] ?? '');
    $dateFrom = isset($dataRequestInfo['dateFrom']) ? date('Y-m-d', strtotime($dataRequestInfo['dateFrom'])) : null;
    $dateTo = isset($dataRequestInfo['dateTo']) ? date('Y-m-d', strtotime($dataRequestInfo['dateTo'])) : null;
    $requestReason = sanitize($dataRequestInfo['requestReason'] ?? '');
    $templateUpload = sanitize($dataRequestInfo['templatePath'] ?? '');
    $compTemplateUlp = $upldFilePth . $templateUpload;
    $templateFileType = sanitize($dataRequestInfo['templateFileType'] ?? '');

    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFiles = [];
    $attachmentNames = $_POST['attachment_names']; // Array of attachment names (req)
    $attachmentTypes = $_POST['attachment_types']; // Array of file types
    foreach ($_FILES['attachments']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['attachments']['error'][$index] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['attachments']['name'][$index]);
            $uniqueFileName = time() . "_" . $fileName;
            $filePath = $uploadDir . $uniqueFileName;
            
            $compFIlePath = $upldFilePth . $filePath;
    
            if (move_uploaded_file($tmpName, $filePath)) {
                $uploadedFiles[] = [
                    "path" => $filePath,
                    "name" => $attachmentNames[$index], // Attach corresponding name
                    "type" => $attachmentTypes[$index]  // Attach corresponding file type
                ];
            } else {
                echo json_encode(["success" => false, "error" => "File upload failed: " . $_FILES['attachments']['error'][$index]]);
                exit();
            }
        }
    }


    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO requestors (fullnames, phone_number, email, requester_type, kra_pin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $names, $phoneNumber, $email, $category, $kraPin); // Adjust types as needed
        if (!$stmt->execute()) {
            throw new Exception("Error inserting requestor: " . $stmt->error);
        }
        $personalInfoId = $stmt->insert_id;

        $year = date("y"); // Get last two digits of the current year (e.g., "25" for 2025)
        $result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(tracking_id, '/', -2) AS UNSIGNED)) AS last_number 
                                FROM requests WHERE tracking_id LIKE 'KRA/CDO/%/$year'");

        $lastNumber = ($result && $row = $result->fetch_assoc()) ? intval($row['last_number']) : 0;
        $newTrackingNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        $trackingID = "KRA/CDO/$newTrackingNumber/$year";

        $stmt2 = $conn->prepare("INSERT INTO requests (tracking_id ,requested_by, description, specific_fields, period_from, period_to, request_purpose, date_requested) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt2->bind_param("sisssss", $trackingID, $personalInfoId, $dataDescription, $specificFields, $dateFrom, $dateTo, $requestReason); // Adjust types as needed
        if (!$stmt2->execute()) {
            throw new Exception("Error inserting request: " . $stmt2->error);
        }
        $requestId = $stmt2->insert_id;

        foreach ($uploadedFiles as $file) {
            $filePath = $file['path'];
            $attachmentName = $file['name'];
            $attachmentType = $file['type'];
        
            $stmt3 = $conn->prepare("INSERT INTO requestors_documents (request_id, requester_id, document_file_path, last_edited_on, document_name, document_type) VALUES (?, ?, ?, NOW(), ?, ?)");
            $stmt3->bind_param("iisss", $requestId, $personalInfoId, $compFIlePath, $attachmentName, $attachmentType);
        
            if (!$stmt3->execute()) {
                throw new Exception("Error inserting document record: " . $stmt3->error);
            }
        }

        $stmt4 = $conn->prepare("UPDATE requestors_documents set request_id = ? , requester_id = ? where document_file_path = ?");
        $stmt4->bind_param("iis", $requestId, $personalInfoId, $ndaUpload); // Adjust types as needed
        if (!$stmt4->execute()) {
            throw new Exception("Error inserting nda: " . $stmt4->error);
        }

        $stmt5 = $conn->prepare("INSERT INTO requestors_documents (document_name, document_type, document_file_path, request_id, requester_id, last_edited_on) VALUES ('Supporting document', ?, ?, ?, ?, NOW())");
        $stmt5->bind_param("ssss",$templateFileType, $compTemplateUlp, $requestId, $personalInfoId); // Adjust types as needed
        if (!$stmt5->execute()) {
            throw new Exception("Error inserting template: " . $stmt5->error);
        }


        $conn->commit();
        echo json_encode(["success" => true, "message" => "Data submitted successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        echo json_encode(["success" => false, "error" => "An error occurred while processing your request. " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method."]);
}
$conn->close();
