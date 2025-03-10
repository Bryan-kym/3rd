<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['attachments'])) {
        echo json_encode(["error" => "No files uploaded"]);
        exit();
    }

    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    // Extract form data from POST
    $category = sanitize($_POST['category'] ?? '');
    $dataDescription = sanitize($_POST['dataDescription'] ?? '');
    $specificFields = sanitize($_POST['specificFields'] ?? '');
    $dateFrom = sanitize($_POST['dateFrom'] ?? '');
    $dateTo = sanitize($_POST['dateTo'] ?? '');
    $requestReason = sanitize($_POST['requestReason'] ?? '');
    
    // Extract Personal Info correctly
    $surname = sanitize($_POST['surname'] ?? '');
    $otherNames = sanitize($_POST['otherNames'] ?? '');
    $names = trim("$surname $otherNames");
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $_POST['email'] : '';
    $phoneNumber = sanitize($_POST['phone'] ?? ''); // Fixed extraction of phone number
    $kraPin = sanitize($_POST['kra_pin'] ?? '');

    // if (empty($names) || empty($email) || empty($phoneNumber)) {
    //     echo json_encode(["error" => "Missing required personal details" . $names . $email . $phoneNumber ]);
    //     exit();
    // }

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
                echo json_encode(["error" => "File upload failed"]);
                exit();
            }
        }
    }

    // Start database transaction
    $conn->begin_transaction();
    try {
        // Insert into `requestors` table
        $stmt = $conn->prepare("INSERT INTO requestors (fullnames, phone_number, email, requester_type, kra_pin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $names, $phoneNumber, $email, $category, $kraPin);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting requestor: " . $stmt->error);
        }
        $personalInfoId = $stmt->insert_id;

        // Insert into `requests` table
        $stmt2 = $conn->prepare("INSERT INTO requests (requested_by, description, specific_fields, period_from, period_to, request_purpose, date_requested) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt2->bind_param("isssss", $personalInfoId, $dataDescription, $specificFields, $dateFrom, $dateTo, $requestReason);
        if (!$stmt2->execute()) {
            throw new Exception("Error inserting request: " . $stmt2->error);
        }
        $requestId = $stmt2->insert_id;

        // Insert uploaded files into `requestors_documents` table
        foreach ($uploadedFiles as $filePath) {
            $stmt3 = $conn->prepare("INSERT INTO requestors_documents (request_id, requester_id, document_file_path) VALUES (?, ?, ?)");
            $stmt3->bind_param("iis", $requestId, $personalInfoId, $filePath);
            if (!$stmt3->execute()) {
                throw new Exception("Error inserting document record: " . $stmt3->error);
            }
        }

        $conn->commit();
        echo json_encode(["success" => "Data submitted successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        echo json_encode(["error" => "An error occurred while processing your request." . $filePath  ]);
    }
}
$conn->close();
