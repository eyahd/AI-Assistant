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

// Ensure data exists before accessing it
$email = isset($_POST['email']) ? trim($_POST['email']) : null;
$password = isset($_POST['password']) ? trim($_POST['password']) : null;

// Validate input
if (empty($email) || empty($password)) {
    die("Error: Email and Password fields cannot be empty.");
}

// Hash password securely
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert into database using prepared statements
$sql = "INSERT INTO users (email, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ss", $email, $hashedPassword);
    
    if ($stmt->execute()) {
        $_SESSION['email'] = $email; // Save email to session
        header("Location: dashboard.html"); // Redirect
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error: Database statement preparation failed.";
}

$conn->close();
?>
