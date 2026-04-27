<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "ccj_sms";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>