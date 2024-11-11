<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "data_requests";

// Create connection
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
