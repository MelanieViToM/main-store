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

    if (($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: home.php');
    exit();
    }

    include('../settings/database.php');

    // Leer filtro desde el desplegable (por defecto: todos)
    $filtro = $_GET['status_filter'] ?? 'all';
$conditions = ["deleted_at IS NULL"];

if ($filtro === 'active') {
    $conditions[] = "status = true";
} elseif ($filtro === 'inactive') {
    $conditions[] = "status = false";
}

$whereSql = "WHERE " . implode(" AND ", $conditions);

$sql = "
    SELECT
        id,
        firstname || ' ' || lastname AS fullname,
        email,
        mobile_phone,
        role,
        CASE WHEN status = true THEN 'Active' ELSE 'Inactive' END AS status,
        url_photo
    FROM
        users
    $whereSql
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
    <title>Users list</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <form method="GET" action="list_users.php" class="mb-3">
        <select name="status_filter" onchange="this.form.submit()" class="form-select w-auto">
            <option value="all" <?php if ($filtro === 'all') echo 'selected'; ?>>List all users</option>
            <option value="active" <?php if ($filtro === 'active') echo 'selected'; ?>>List active users only</option>
            <option value="inactive" <?php if ($filtro === 'inactive') echo 'selected'; ?>>List inactive users only</option>
        </select>
    </form>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Fullname</th>
                <th>Email</th>
                <th>Mobile phone</th>
                <th>Role</th>
                <th>Status</th>
                <th>Options</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = pg_fetch_assoc($res)) { ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($row['url_photo'] ?: 'photos/photo_default.png', ENT_QUOTES) ?>" width="30" alt="User photo profile">
                    </td>
                    <td><?= htmlspecialchars($row['fullname'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['email'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['mobile_phone'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['role'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['status'], ENT_QUOTES) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= (int)$row['id'] ?>">
                            <img src="icons/edit.png" alt="Edit user" width="20">
                        </a>
                        <a href="delete_user.php?userID=<?= (int)$row['id'] ?>"
                           onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($row['fullname'], ENT_QUOTES) ?>?');">
                            <img src="icons/delete.png" alt="Delete user" width="20">
                        </a>
                        <a href="update_user_status.php?userID=<?= (int)$row['id'] ?>&userStatus=<?= urlencode($row['status']) ?>">
                            <?php if ($row['status'] === 'Active') { ?>
                                <img src="icons/disable.png" alt="Inactive user" width="20">
                            <?php } else { ?>
                                <img src="icons/enable.png" alt="Active user" width="20">
                            <?php } ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <a href="home.php" class="btn btn-link ps-0">Go to home</a>
</body>
</html>