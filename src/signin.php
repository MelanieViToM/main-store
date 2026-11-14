<?php
//Get database connection
include('../settings/database.php');

session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    //Get payload from frontend
    $email = $_POST['email'];
    $password = MD5($_POST['pswd']);

    //First, We check whether the email exists in the database.
    $sql_check_email = "SELECT * FROM users WHERE email = '$email'";
    $res_email = pg_query($local_conn, $sql_check_email);

    if(pg_num_rows($res_email)>0){
        //The email DOES exist, now we will verify whether the password matches that email
        $sql_check_user = "
            SELECT
                id,
                firstname,
                lastname,
                email,
                url_photo
            FROM
                users
            WHERE 
                email = '$email'and
                password = '$password'
            ";
        $res_user = pg_query($local_conn, $sql_check_user);
        $total_user = pg_num_rows($res_user);
        

        if ($total_user > 0){
            //Correct credentials: logging in
            $row = pg_fetch_assoc($res_user);
            $_SESSION['user_id'] = $row[ 'id'];
            $_SESSION['user_firstname'] = $row['firstname'];
            $_SESSION['user_lastname'] = $row['lastname'];
            header('refresh:0;url=home.php');
            exit();
        } else{
            //The email exists but the password is incorrect
            echo"<script>alert('Invalid password. Try again !!!')</script>";
            header('refresh:0;url=signin.html');
            exit();
        }
    }else{
        //The email does not exist in the database
        echo "<script>alert('The entered email is not registered.')</script>";
        header('refresh:0;url=signin.html');
    }
}

