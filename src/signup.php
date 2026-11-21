<?php
//Get database connection
    session_start();
    // Verificar si el usuario ha iniciado sesión; si no, redirigirlo al login
    if (!isset($_SESSION['user_id'])) {
        header('Location: signin.html');
        exit();
    }
require('../settings/database.php');

//Get payload from frontend
    $first_name = $_POST['fname'];
    $last_name = $_POST['lname'];
    $ident_number = $_POST['idnumber'];
    $mobile_phone = $_POST['mphone'];
    $email = $_POST['email'];
    $password = MD5($_POST['pswd']);
    $PHOTO_URL = 'photos/photo_default.png';

$sql_check_email = "SELECT * FROM users WHERE email = '$email' ";
$res = pg_query($local_conn, $sql_check_email);
$total = pg_num_rows($res);
if ($total < 1){
    //Prepare query
    $sql = "INSERT INTO users
            (firstname, lastname, ident_number, mobile_phone, email, password, url_photo)
        values(
            '$first_name',
            '$last_name',
            '$ident_number',
            '$mobile_phone',
            '$email',
            '$password',
            '$PHOTO_URL'
            );
        ";
    
        //Execute query
        $res = pg_query($local_conn, $sql);
        
        if($res){
            //echo "<br>user has been created successfully !!!";
            echo "<script> alert('user has been created successfully !!!')</script>";
            header('refresh:0;url=signin.html');
        } else{
            echo"<br>Error: user hasn't been created";
        }
}else{
    echo"<script>alert('user email already exists. Try again !!!')</script>";
    header('refresh:0;url=signup.html');
}

