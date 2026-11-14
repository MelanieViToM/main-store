<?php
// Iniciar la sesión para poder acceder a las variables almacenadas
session_start();

// Verificar si el usuario ha iniciado sesión; si no, redirigirlo al login
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.html');
    exit();
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
<head>
<body>
    <center><h3>Welcome to Main Store</h3>
    <a href="logout.php">Logout</a>
    </center>
<body>
</html>