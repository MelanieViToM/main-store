<?php
/*
Use this Endpoint to test request
http://localhost/main-store/src/edit_user.php?id=<?php
*/
require('../settings/database.php');

$id = $_GET['id'];   // el número que llegó en la URL

$sql_edit_user = 'SELECT * FROM users WHERE id = $1';
$result = pg_query_params($local_conn, $sql_edit_user, array($id));
$user = pg_fetch_assoc($result);   // una sola fila: el usuario a editar
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Edit user</title>
</head>
<body>
    <h1>Edit user</h1>

    <form action="update_user.php" method="post" enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

        <label>Name</label><br>
        <input type="text" name="firstname" value="<?php echo $user['firstname']; ?>" required><br><br>

        <label>Last name</label><br>
        <input type="text" name="lastname" value="<?php echo $user['lastname']; ?>" required><br><br>

        <label>Email</label><br>
        <input type="email" name="email" value="<?php echo $user['email']; ?>" required><br><br>

        <label>Mobile phone</label><br>
        <input type="text" name="mobile_phone" value="<?php echo $user['mobile_phone']; ?>" required><br><br>

        <?php if ($user['url_photo'] != "") { ?>
            <p>Foto actual:</p>
            <img src="<?php echo $user['url_photo']; ?>" width="120"><br><br>
        <?php } ?>

        <label>Photo (JPG or PNG)</label><br>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png"><br><br>

        <button type="submit">Save</button>
        <button type="button" onclick="window.location.href='list_users.php'">Cancel</button>
    </form>
</body>
</html>
