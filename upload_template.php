<?php
include 'config.php';
// Define the upload directory
// $uploadDir = 'uploads/';


// Check if the file was uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Get the file name
    $fileName = $_POST['fileName'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $filePath = $uploadDir . $fileName;

    // Move the file to the upload directory
    if (move_uploaded_file($fileTmpName, $filePath)) {
        // Return the file path
        echo json_encode(["success" => true, "filePath" => $filePath]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to move uploaded file."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "No file uploaded or upload error."]);
}
?>