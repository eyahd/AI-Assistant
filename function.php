<?php

function check_login($conn)
{
    // Check if the user is logged in
    if (isset($_SESSION['user_id'])) {

        $id = $_SESSION['user_id'];

        // Protect against SQL injection (though $id is from session)
        $id = mysqli_real_escape_string($conn, $id);

        $query = "SELECT * FROM users WHERE user_id = '$id' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            return $user_data;
        }
    }

    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

function random_num($length)
{
    $text = "";

    if ($length < 5) {
        $length = 5;
    }

    $len = rand(4, $length);

    for ($i = 0; $i < $len; $i++) {
        $text .= rand(0, 9);
    }

    return $text;
}
