<?php
//Get database connection
require('../settings/database.php');

//Get payload from frontend
    $first_name = $_POST['fname'];
    $last_name = $_POST['lname'];
    $ident_number = $_POST['idnumber'];
    $mobile_phone = $_POST['mphone'];
    $email = $_POST['email'];
    $password = MD5($_POST['pswd']);

$sql_check_email = "SELECT * FROM users WHERE email = '$email' ";
$res = pg_query($local_conn, $sql_check_email);
$total = pg_num_rows($res);
if ($total < 1){
    //Prepare query
    $sql = "INSERT INTO users
            (firstname, lastname, mobile_phone, ident_number, email, password)
        values(
            '$first_name',
            '$last_name',
            '$ident_number',
            '$mobile_phone',
            '$email',
            '$password'
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

