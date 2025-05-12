<?php
session_start();
include("connection.php");
include("function.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password) && !is_numeric($email)) {

        // Use prepared statements to avoid SQL injection
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);

            // Verify hashed password
            if (password_verify($password, $user_data['password'])) {
                $_SESSION['email'] = $user_data['email'];
                header("Location: user.php");
                exit;
            } else {
                echo "Wrong email or password!";
            }
        } else {
            echo "Wrong email or password!";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Wrong email or password!";
    }
}
?>
