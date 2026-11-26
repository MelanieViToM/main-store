<?php
/*
Use this Endpoint to test request
http://localhost/main-store/src/update_user_status.php?userID=ANY-ID-NUMBER
*/
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: home.php');
    exit();
}

include('../settings/database.php');

$userId = filter_input(INPUT_GET, 'userID', FILTER_VALIDATE_INT);
$userStatus = $_GET['userStatus'] ?? '';

if ($userId) {
    // Evitar que un administrador se desactive a sí mismo por accidente
    if ((int)$userId === (int)$_SESSION['user_id']) {
        echo "<script>alert('You cannot change your own active status.'); window.location='list_users.php';</script>";
        exit();
    }

    $newStatus = ($userStatus === 'Active') ? 'false' : 'true';

    $sql = "
        UPDATE users
        SET status = $1, updated_at = NOW()
        WHERE id = $2 AND deleted_at IS NULL
    ";
    $res = pg_query_params($local_conn, $sql, [$newStatus, $userId]);

    if ($res) {
        echo "<script>alert('User status updated successfully.'); window.location='list_users.php';</script>";
        exit();
    } else {
        $err = htmlspecialchars(pg_last_error($local_conn), ENT_QUOTES);
        echo "<script>alert('Error updating status: {$err}'); window.location='list_users.php';</script>";
        exit();
    }
}

header('Location: list_users.php');
exit();
?>