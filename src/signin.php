<?php
//Get database connection
/*
    Use this URL to in browser to register a new user:
    http://localhost/main-store/src/signin.html
*/
include('../settings/database.php');

session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    //Get payload from frontend
    $email = trim($_POST['email'] ?? '');
    $plain_password = $_POST['pswd'] ?? '';

    //First, We check whether the email exists in the database and fetch its stored hash.
    $sql_check_email = "
        SELECT
            id,
            firstname,
            lastname,
            email,
            password,
            role,
            status,
            url_photo
        FROM
            users
        WHERE
            email = $1
            AND deleted_at IS NULL
        LIMIT 1
    ";
    $res_email = pg_query_params($local_conn, $sql_check_email, [$email]);

    if (!$res_email || pg_num_rows($res_email) === 0) {
        echo "<script>alert('The entered email is not registered.'); window.location='signin.html';</script>";
        exit();
    }
        $row = pg_fetch_assoc($res_email);

        $isActive = ($row['status'] === 't' || $row['status'] === true || $row['status'] === 'true');
    if (!$isActive) {
        echo "<script>alert('This user account is inactive.'); window.location='signin.html';</script>";
        exit();
    }

    //The email DOES exist, now we verify whether the password matches the stored hash
    if (password_verify($plain_password, $row['password'])) {
        //Correct credentials: logging in
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_firstname'] = $row['firstname'];
        $_SESSION['user_lastname'] = $row['lastname'];
        $_SESSION['user_role'] = $row['role'] ?? 'user';
        $_SESSION['user_photo'] = $row['url_photo'] ?? 'photos/photo_default.png';
        header('refresh:0;url=home.php');
        exit();
    } else {
            //The email exists but the password is incorrect
        echo "<script>alert('Invalid password. Try again !!!'); window.location='signin.html';</script>";
        exit();
    }
}
?>

