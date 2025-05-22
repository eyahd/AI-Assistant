<?php
$host = 'localhost';
$user = 'root'; // or your DB username
$pass = ''; // or your DB password
$dbname = 'user_registration';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>