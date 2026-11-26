<?php

//Get database connection
session_start();
// Verificar si el usuario ha iniciado sesión; si no, redirigirlo al login
/*if (!isset($_SESSION['user_id'])) {
    header('Location: signin.html');
    exit();
}*/

//Get database connection
require('../settings/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get payload from frontend
    $first_name = trim($_POST['fname'] ?? '');
    $last_name = trim($_POST['lname'] ?? '');
    $ident_number = trim($_POST['idnumber'] ?? '');
    $mobile_phone = trim($_POST['mphone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plain_password = $_POST['pswd'] ?? '';
    $password_hash = password_hash($plain_password, PASSWORD_DEFAULT);
    $PHOTO_URL = 'photos/photo_default.png';

    // Verificar si el correo ya existe
    $sql_check = "SELECT id FROM users WHERE email = $1 AND deleted_at IS NULL";
    $res_check = pg_query_params($local_conn, $sql_check, [$email]);

    if (!$res_check) {
        die("Error cheking email: " . pg_last_error($local_conn));
    }

    if (pg_num_rows($res_check) > 0) {
        echo "<script>alert('User email already exists. Try again !!!'); window.location='signup.html';</script>";
        exit();
    }

    $sql_insert = "
        INSERT INTO users
            (firstname, lastname, ident_number, mobile_phone, email, password, url_photo, status, role)
        VALUES
            ($1, $2, $3, $4, $5, $6, $7, true, 'user')
    ";

    $res_insert = pg_query_params($local_conn, $sql_insert, [
        $first_name,
        $last_name,
        $ident_number,
        $mobile_phone,
        $email,
        $password_hash,
        $PHOTO_URL
    ]);

    if ($res_insert) {
        echo "<script>alert('User has been created successfully !!!'); window.location='signin.html';</script>";
        exit();
    } else {
        $err = htmlspecialchars(pg_last_error($local_conn), ENT_QUOTES);
        echo "<script>alert('Error: User has not been created. DB error: {$err}'); window.location='signup.html';</script>";
        exit();
    }
}
?>


