<?php
/*
Use this Endpoint to test request
http://localhost/main-store/src/delete_user.php?userID=ANY-ID-NUMBER
*/
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: home.php');
    exit();
}

include('../settings/database.php');

$userId = filter_input(INPUT_GET, 'userID', FILTER_VALIDATE_INT);

if ($userId) {
    // Evitar que un administrador se elimine a sí mismo
    if ((int)$userId === (int)$_SESSION['user_id']) {
        echo "<script>alert('You cannot delete your own account.'); window.location='list_users.php';</script>";
        exit();
    }

    $sql = "
        UPDATE users
        SET deleted_at = NOW(), updated_at = NOW(), status = false
        WHERE id = $1 AND deleted_at IS NULL
    ";
    $res = pg_query_params($local_conn, $sql, [$userId]);

    if ($res) {
        echo "<script>alert('User deleted successfully.'); window.location='list_users.php';</script>";
        exit();
    } else {
        $err = htmlspecialchars(pg_last_error($local_conn), ENT_QUOTES);
        echo "<script>alert('Error deleting user: {$err}'); window.location='list_users.php';</script>";
        exit();
    }
}

header('Location: list_users.php');
exit();
?>
