<?php
require('../settings/database.php');

// Datos que llegan del formulario
$id           = $_POST['id'];
$firstname    = $_POST['firstname'];
$lastname     = $_POST['lastname'];
$email        = $_POST['email'];
$mobile_phone = $_POST['mobile_phone'];

// ---------- PARTE A: actualizar los datos de texto (sin tocar url_photo)----------
$sql = 'UPDATE users
        SET firstname = $1, lastname = $2, email = $3, mobile_phone = $4
        WHERE id = $5';
$result = pg_query_params($local_conn, $sql, array($firstname, $lastname, $email, $mobile_phone, $id));

if (!$result) {
    echo "Error al actualizar los datos";
    exit;
}

// ---------- PARTE B: la foto (solo si eligieron una) ----------
if ($_FILES['photo']['name'] != "") {

    // extensión del archivo en minúsculas: jpg, jpeg o png
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    if ($ext == "jpg" || $ext == "jpeg" || $ext == "png") {

        // nombre de la foto = número de celular + extensión
        $photo_url = "photos/" . $mobile_phone . "." . $ext;

        // mover la foto de la carpeta temporal a src/photos
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_url)) {

            // guardar la ruta en la base de datos
            $sql = 'UPDATE users SET url_photo = $1 WHERE id = $2';
            pg_query_params($local_conn, $sql, array($photo_url, $id));

        } else {
            echo "No se pudo guardar la foto. Revisa la carpeta photos.";
            exit;
        }

    } else {
        echo "Solo se permiten fotos JPG o PNG. <a href='edit_user.php?id=$id'>Volver</a>";
        exit;
    }
}

// ---------- PARTE C: volver a la lista ----------
header("Location: list_users.php");
exit;
?>