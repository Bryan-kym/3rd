<?php
require_once __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$servername = $_SERVER['DBSERVERNAME'];
$username = $_SERVER['DBUSERNAME'];
$password = $_SERVER['DBPASSWORD'];
$dbname = $_SERVER['DBNAME'];

// Create connection s
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set("Africa/Nairobi");
$timestamptoday = date("Y-m-d H:i:s");
$timeonly = date("H:i:s");
$date_stamp = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("$date_stamp -1 day"));
$documents_file_path = $_SERVER['DOCUMENTS_FILE_PATH'];
