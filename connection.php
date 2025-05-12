<?php

$host = 'localhost';
$db = 'user_registration'; // your database name
$user = 'root'; // default for XAMPP
$pass = ''; // default for XAMPP
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>