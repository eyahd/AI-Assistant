<?php
session_start();
// Connect to MySQL
$host = 'localhost';
$db = 'user_registration'; // your database name
$user = 'root'; // default for XAMPP
$pass = ''; // default for XAMPP
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form inputs safely
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash password

// Insert into database
$sql = "INSERT INTO users (email, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);

if ($stmt->execute()) {
    echo "Registration successful!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
