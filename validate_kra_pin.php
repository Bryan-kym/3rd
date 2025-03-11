<?php
require 'vendor/autoload.php'; // Load Composer's autoloader

// Load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get the KRA PIN from the POST request
$kraPin = $_POST['kra_pin'];

// SOAP request URL
$soapUrl  = $_SERVER['PINCHECKER_URL'];

// SOAP request XML template
$soapRequest = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:impl="http://impl.external.intf.kra.tcs.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <impl:validatePIN>
         <!--Optional:-->
         <loginId>{$_SERVER['PINCHEKER_USERNAME']}</loginId>
         <!--Optional:-->
         <password>{$_SERVER['PINCHEKER_PASSWORD']}</password>
         <!--Optional:-->
         <KRAPIN>$kraPin</KRAPIN>
      </impl:validatePIN>
   </soapenv:Body>
</soapenv:Envelope>
XML;

// Set up the HTTP headers
$headers = [
    "Content-Type: text/xml; charset=utf-8",
    "SOAPAction: \"\"", // Replace with the actual SOAP action if required
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $soapUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $soapRequest);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the SOAP request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    die(json_encode(["status" => "error", "message" => "cURL Error: " . curl_error($ch)]));
}

// Close cURL
curl_close($ch);

// Parse the SOAP response
$xml = simplexml_load_string($response);
if ($xml === false) {
    die(json_encode(["status" => "error", "message" => "Failed to parse XML response"]));
}

// Extract the CDATA section from the response
$cdata = (string)$xml->xpath('//ResponseForValidationOfPIN')[0];

// Parse the CDATA section as XML
$cdataXml = simplexml_load_string($cdata);
if ($cdataXml === false) {
    die(json_encode(["status" => "error", "message" => "Failed to parse CDATA section"]));
}

// Check if the KRA PIN is valid
$responseCode = (string)$cdataXml->RESULT->ResponseCode;
if ($responseCode === "23000") {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid KRA PIN"]);
}
?>