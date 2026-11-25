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
    $first_name = $_POST['fname'] ?? '';
    $last_name = $_POST['lname'] ?? '';
    $ident_number = $_POST['idnumber'] ?? '';
    $mobile_phone = $_POST['mphone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password_hash = password_hash($_POST['pswd'] ?? '', PASSWORD_DEFAULT);
    $PHOTO_URL = 'photos/photo_default.png';

    // Verificar si el correo ya existe
    $sql_check_email = "SELECT id FROM users WHERE email = $1";
    $res_check = pg_query_params($local_conn, $sql_check_email, [$email]);

    if (!$res_check) {
        die("Error al consultar correo: " . pg_last_error($local_conn));
    }

    if (pg_num_rows($res_check) < 1) {
        // Insertar usuario con el hash seguro
        $sql = "INSERT INTO users
                    (firstname, lastname, ident_number, mobile_phone, email, password, url_photo)
                VALUES
                    ($1, $2, $3, $4, $5, $6, $7)";

        $res_insert = pg_query_params($local_conn, $sql, [
            $first_name,
            $last_name,
            $ident_number,
            $mobile_phone,
            $email,
            $password_hash,
            $PHOTO_URL
        ]);

        if ($res_insert) {
            echo "<script>alert('user has been created successfully !!!'); window.location='signin.html';</script>";
            exit();
        } else {
            $err = htmlspecialchars(pg_last_error($local_conn), ENT_QUOTES);
            echo "<script>alert('Error: user has not been created. DB error: {$err}'); window.location='signup.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('user email already exists. Try again !!!'); window.location='signup.html';</script>";
        exit();
    }
}
?>


