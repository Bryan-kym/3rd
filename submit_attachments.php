<?php
include 'config.php'; // Your database connection details
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //For debugging; remove for production

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    $personalInfo = json_decode($_POST['personalInfo'], true);
    $dataRequestInfo = json_decode($_POST['dataRequestInfo'], true);
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

    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFiles = [];
    foreach ($_FILES['attachments']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['attachments']['error'][$index] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['attachments']['name'][$index]);
            $uniqueFileName = time() . "_" . $fileName;
            $filePath = $uploadDir . $uniqueFileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                $uploadedFiles[] = $filePath;
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
        $stmt2->bind_param("sisssss",$trackingID, $personalInfoId, $dataDescription, $specificFields, $dateFrom, $dateTo, $requestReason); // Adjust types as needed
        if (!$stmt2->execute()) {
            throw new Exception("Error inserting request: " . $stmt2->error);
        }
        $requestId = $stmt2->insert_id;

        foreach ($uploadedFiles as $filePath) {
            $stmt3 = $conn->prepare("INSERT INTO requestors_documents (request_id, requester_id, document_file_path) VALUES (?, ?, ?)");
            $stmt3->bind_param("iis", $requestId, $personalInfoId, $filePath);
            if (!$stmt3->execute()) {
                throw new Exception("Error inserting document record: " . $stmt->error);
            }
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
?>
