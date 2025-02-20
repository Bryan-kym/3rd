<?php
// Include the necessary configuration file to connect to the database
include 'config.php';

// Set content type to JSON for API response
header('Content-Type: application/json');

// Get the raw POST data (JSON) sent from JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Check if the data contains 'pdf' and 'name'
if (isset($data['pdf']) && isset($data['name'])) {
    $pdfData = $data['pdf'];  // Base64-encoded PDF datas
    $name = $data['name'];    // User's name as the signature
    $file_dbname = 'NDA form';
    $filetype = 'application/pdf';

    // Generate a unique name once
    $uniqueName = uniqid('nda_'); // e.g. nda_5f2b9c7a9d2e1
    // Path to save the PDF file
    $filePath = 'uploads/' . $uniqueName . '.pdf';
    $filePathdb = '3rd-be/uploads/' . $uniqueName . '.pdf';

    // Decode the Base64 data and save the PDF to the server
    if (file_put_contents($filePath, base64_decode($pdfData))) {
        // Prepare SQL query to insert data into the database
        $stmt = $conn->prepare("INSERT INTO requestors_documents (document_name, document_file_path, last_edited_on, document_type) VALUES (?, ?, ? , ?)");
        if ($stmt) {
            // Bind parameters and execute the query
            $stmt->bind_param("ssss", $file_dbname, $filePathdb, $timestamptoday, $filetype);
            if ($stmt->execute()) {
                // Success - return file path and success status as JSON
                echo json_encode(['success' => true, 'filePath' => $filePathdb]);
            } else {
                // Failure - unable to insert into database
                echo json_encode(['success' => false, 'message' => 'Failed to save data to database']);
            }
            $stmt->close();
        } else {
            // Error preparing statement
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        // Failure - unable to save the PDF file
        echo json_encode(['success' => false, 'message' => 'Failed to save PDF']);
    }
} else {
    // Error: missing data
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}

// Close database connection
$conn->close();
