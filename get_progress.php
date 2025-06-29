<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo "Not logged in.";
    exit();
}

$username = $_SESSION['username'];

$conn = new mysqli("localhost", "root", "", "user_registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT course_id, lesson_id, progress, last_updated FROM progress WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row; // Keep as list of objects
}

echo json_encode($data);
?>
