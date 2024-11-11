<?php
require('libs/fpdf.php');

// Get name from POST request
$name = $_POST['name'] ?? '';

// Define NDA content
$ndaContent = "
Non-Disclosure Agreement (NDA)

This Agreement is made between [Organization Name] and the user. By agreeing to this NDA, you commit not to disclose any proprietary or confidential information shared by [Organization Name] during the course of this application.

Your acceptance of these terms is required to proceed with the request for information. This NDA is legally binding and will be enforceable in accordance with the laws of the applicable jurisdiction.

By typing your name in the box below and clicking \"I Agree,\" you confirm your consent to the terms outlined in this agreement.

Signature: $name
";

// Create a new PDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Add NDA content to PDF
$pdf->MultiCell(0, 10, $ndaContent);

// Define the file path and name
$filePath = 'saved_pdfs/';
$fileName = 'NDA_' . time() . '.pdf';
$fullPath = $filePath . $fileName;

// Ensure the directory exists
if (!is_dir($filePath)) {
    mkdir($filePath, 0777, true);
}

// Save the PDF to the specified folder
$pdf->Output('F', $fullPath);

// Return response with PDF file path
echo json_encode(['filePath' => $fullPath]);
?>
