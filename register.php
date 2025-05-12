<?php 
session_start();

include("connection.php");
include("function.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];

    if (!empty($user_name) && !empty($password) && !is_numeric($user_name)) {
        // Create a unique user ID
        $user_id = random_num(20);

        // Hash the password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Use prepared statement
        $query = "INSERT INTO users (user_id, user_name, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "sss", $user_id, $user_name, $hashed_password);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: login.php");
            exit;
        } else {
            echo "Error: Could not register user.";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Please enter some valid information!";
    }
}
?>
