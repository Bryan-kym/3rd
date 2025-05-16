<?php
header('Content-Type: application/json');
require_once '../auth.php';
require_once '../config.php';

try {
    $userId = authenticate();
    $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);

    if (!$requestId) {
        throw new Exception('Invalid request ID');
    }

    // Validate user owns this request
    $stmt = $conn->prepare("
SELECT r.id, r.requested_by
FROM requests r
JOIN requestors req ON r.requested_by = req.id
WHERE r.id = ? AND req.email = (SELECT email FROM ext_users WHERE id = ?)
AND r.request_status in ('pending', 'rejected')
");
    $stmt->bind_param("ii", $requestId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $requestData = $result->fetch_assoc();

    if (!$requestData) {
        throw new Exception('Request not found or cannot be edited');
    }

    // Now you can access the requested_by value
    $requesterId = $requestData['requested_by'];

    // Get and sanitize input
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $specificFields = filter_input(INPUT_POST, 'specific_fields', FILTER_SANITIZE_STRING);
    $requestPurpose = filter_input(INPUT_POST, 'request_purpose', FILTER_SANITIZE_STRING);

    // Update request
    $updateStmt = $conn->prepare("
        UPDATE requests 
        SET description = ?, specific_fields = ?, request_purpose = ?, request_status = 'resubmitted', tracking_status = 'resubmitted'
        WHERE id = ?
    ");
    $updateStmt->bind_param("sssi", $description, $specificFields, $requestPurpose, $requestId);
    $updateStmt->execute();

    echo json_encode(['success' => true, 'message' => 'Request updated successfully']);

    // Handle required documents upload
    if (!empty($_FILES['required_docs'])) {
        foreach ($_FILES['required_docs']['name'] as $docName => $fileData) {
            if ($_FILES['required_docs']['error'][$docName] === UPLOAD_ERR_OK) {
                $fileName = basename($_FILES['required_docs']['name'][$docName]);
                $filePath = '../' . $uploadDir . uniqid() . '_' . $fileName;
                $filepathdb = $uploadDir . uniqid() . '_' . $fileName;

                if (move_uploaded_file($_FILES['required_docs']['tmp_name'][$docName], $filePath)) {
                    $insertDoc = $conn->prepare("
                    INSERT INTO requestors_documents 
                    (request_id, requester_id, document_name, document_file_path) 
                    VALUES (?, ?, ?, ?)
                ");
                    $insertDoc->bind_param(
                        "iiss",
                        $requestId,
                        $requesterId,  // Use the fetched requester_id
                        $docName,
                        $filePathdb
                    );
                    $insertDoc->execute();
                }
            }
        }
    }

    // Handle additional documents upload
    if (!empty($_FILES['additional_docs'])) {
        foreach ($_FILES['additional_docs']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['additional_docs']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = basename($_FILES['additional_docs']['name'][$key]);
                $docName = $_POST['additional_doc_names'][$key] ?? $fileName;
                $filePath = '../' . $uploadDir . uniqid() . '_' . $fileName;
                $filepathdb = $uploadDir . uniqid() . '_' . $fileName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $insertDoc = $conn->prepare("
                    INSERT INTO requestors_documents 
                    (request_id, requester_id, document_name, document_file_path) 
                    VALUES (?, ?, ?, ?)
                ");
                    $insertDoc->bind_param(
                        "iiss",
                        $requestId,
                        $requesterId,  // Use the fetched requester_id
                        $docName,
                        $filePathdb
                    );
                    $insertDoc->execute();
                }
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
