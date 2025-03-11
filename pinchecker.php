<?php
include 'config.php';
// SOAP request URL
$soapUrl  = $_SERVER['PINCHECKER_URL'];
$password = $_SERVER['PINCHEKER_PASSWORD'];
$username = $_SERVER['PINCHEKER_USERNAME'];

// SOAP request XML template
$soapRequest = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:impl="http://impl.external.intf.kra.tcs.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <impl:validatePIN>
         <!--Optional:-->
         <loginId>{username}</loginId>
         <!--Optional:-->
         <password>{Password}</password>
         <!--Optional:-->
         <KRAPIN>{KRAPIN}</KRAPIN>
      </impl:validatePIN>
   </soapenv:Body>
</soapenv:Envelope>
XML;

// Get KRAPIN from POST request
$input = json_decode(file_get_contents('php://input'), true);
$krapin = $input['KRAPIN'] ?? '';


$soapRequest = str_replace("{KRAPIN}", $krapin, $soapRequest);
$soapRequest = str_replace("{Password}", $password, $soapRequest);
$soapRequest = str_replace("{username}", $username, $soapRequest);

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
    die("cURL Error: " . curl_error($ch));
}

// Close cURL
curl_close($ch);

// Parse the SOAP response
$xml = simplexml_load_string($response);
if ($xml === false) {
    die("Failed to parse XML response");
}

// Extract the CDATA section from the response
$cdata = (string)$xml->xpath('//ResponseForValidationOfPIN')[0];

// Parse the CDATA section as XML
$cdataXml = simplexml_load_string($cdata);
if ($cdataXml === false) {
    die("Failed to parse CDATA section");
}

// Convert the CDATA XML to JSON
$jsonResponse = json_encode($cdataXml, JSON_PRETTY_PRINT);

// Output the JSON response
header("Content-Type: application/json");
echo $jsonResponse;
?>