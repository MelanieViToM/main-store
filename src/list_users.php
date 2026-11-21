<?php
/*
    Use this URL to print data in browser:
    http://localhost/main-store/src/list_users.php
    */
    session_start();
    // Verificar si el usuario ha iniciado sesión; si no, redirigirlo al login
    if (!isset($_SESSION['user_id'])) {
        header('Location: signin.html');
    exit();
    }

    include('../settings/database.php');

    // Leer filtro desde el desplegable (por defecto: todos)
    $filtro = $_GET['status_filter'] ?? 'all';

    $where = "";
    if ($filtro === 'active') {
        $where = " WHERE status = true";
    } elseif ($filtro === 'inactive') {
        $where = " WHERE status = false";
    }

    //Get data from users table
    $sql="
        select 
            id,
            firstname || ' ' || lastname as fullname,
            email,
            mobile_phone,
            status,
            case when status = true then 'Active' else 'Inactive' end as status,
            url_photo
        from
            users
        $where
        ORDER BY
            id ASC
    ";
    $res = pg_query($local_conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign in</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <form method="GET" action="list_users.php" class="m-2">
            <select name="status_filter" onchange="this.form.submit()" class="form-select w-auto">
                <option value="all" <?php if ($filtro === 'all') echo 'selected'; ?>>List all users</option>
                <option value="active" <?php if ($filtro === 'active') echo 'selected'; ?>>List active users only</option>
                <option value="inactive" <?php if ($filtro === 'inactive') echo 'selected'; ?>>List inactive users only</option>
            </select>
        </form>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Fullname</th>
                    <th>Email</th>
                    <th>Mobile phone</th>
                    <th>Status</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row= pg_fetch_assoc($res)) { ?>
                    <tr>
                        <!-- Here print data from database -->
                        <td>
                            <img src = "<?= $row['url_photo'] ?>" width="30" alt="User photo profile">
                        </td>
                        <td><?= htmlspecialchars($row['fullname'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($row['email'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($row['mobile_phone'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($row['status'], ENT_QUOTES) ?></td>
                        <td>
                            <a href = "edit_user.php?id=<?=$row['id']?>">
                                <img src ="icons/edit.png" alt="Edit user" width="20">
                            </a>
                            <a href = "#">
                                <img src ="icons/search.png" alt="Search user" width="20">
                            </a>
                            <a href = "delete_user.php?userID=<?= $row['id'] ?>">
                            <a href = "delete_user.php?userID=<?= $row['id']?>"
                                onclick="return confirm('¿Are you sure you want to delete to <?= htmlspecialchars($row['fullname'], ENT_QUOTES) ?>?');">
                                <img src ="icons/delete.png" alt="Delete user" width="20">
                            </a>
                            <a href = "update_user_status.php?userID=<?= $row['id'] ?>&userStatus=<?=$row['status'] ?>">
                                <?php
                                    if($row['status'] == 'Active'){
                                    echo "<img src='icons/disable.png' alt='Inactive user' width='20'>";
                                    }else{
                                        echo "<img src='icons/enable.png' alt='Active user' width='20'>";
                                    }     
                                ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?> 
            </tbody>    
        </table>
        <a href="home.php" class="m-2 d-inline-block">Go to home</a>
    </body>
</html>
