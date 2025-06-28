<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo "Not logged in";
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $topic = $_POST['topic'];
    $progress = intval($_POST['progress']);

    $conn = new mysqli("localhost", "root", "", "user_registration");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if record exists
    $stmt = $conn->prepare("SELECT * FROM dashboard WHERE username = ? AND topic = ?");
    $stmt->bind_param("ss", $username, $topic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update progress
        $update = $conn->prepare("UPDATE dashboard SET progress = ?, last_active = NOW() WHERE username = ? AND topic = ?");
        $update->bind_param("iss", $progress, $username, $topic);
        $update->execute();
        echo "Progress updated";
    } else {
        // Insert new progress
        $insert = $conn->prepare("INSERT INTO dashboard (username, topic, progress, last_active) VALUES (?, ?, ?, NOW())");
        $insert->bind_param("ssi", $username, $topic, $progress);
        $insert->execute();
        echo "Progress saved";
    }

    $conn->close();
}
?>
