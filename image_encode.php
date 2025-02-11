<?php
// Path to the image file
$imagePath = 'assets/images/pdf_header.png';

// Check if the image exists
if (file_exists($imagePath)) {
    // Read image file and encode it to Base64
    $imageData = base64_encode(file_get_contents($imagePath));
    // Output the Base64-encoded image
    echo "data:image/png;base64, $imageData";
} else {
    echo "Image not found!";
}
?>
