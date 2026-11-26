<?php
/*
Use this Endpoint to test request
http://localhost/main-store/src/edit_user.php?id=<?php
*/
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.html');
    exit();
}

include('../settings/database.php');

$sessionUserId = (int)$_SESSION['user_id'];
$sessionRole = $_SESSION['user_role'] ?? 'user';
$isAdmin = ($sessionRole === 'admin');

$targetId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: $sessionUserId;

// Si no es admin, solo puede ver/editar su propio perfil
if (!$isAdmin && $targetId !== $sessionUserId) {
    header('Location: edit_user.php?id=' . $sessionUserId);
    exit();
}

$sqlSelect = "
    SELECT id, firstname, lastname, ident_number, mobile_phone, email, role, status, url_photo
    FROM users
    WHERE id = $1 AND deleted_at IS NULL
    LIMIT 1
";
$resSelect = pg_query_params($local_conn, $sqlSelect, [$targetId]);

if (!$resSelect || pg_num_rows($resSelect) === 0) {
    $backUrl = $isAdmin ? 'list_users.php' : 'home.php';
    echo "<script>alert('User not found.'); window.location='{$backUrl}';</script>";
    exit();
}

$user = pg_fetch_assoc($resSelect);
$isUserActive = ($user['status'] === 't' || $user['status'] === true || $user['status'] === 'true');
$currentPhoto = $user['url_photo'] ?: 'photos/photo_default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container" style="max-width: 600px;">
        <h1 class="h3 mb-3"><?= $isAdmin ? 'Edit User #' . (int)$user['id'] : 'Edit My Profile' ?></h1>

        <div class="mb-3">
            <img src="<?= htmlspecialchars($currentPhoto, ENT_QUOTES) ?>"
                 width="80" class="img-thumbnail" alt="User photo profile">
        </div>

        <form method="POST" action="update_user.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

            <div class="mb-3">
                <label class="form-label">First name</label>
                <input type="text" name="firstname" class="form-control" required
                       value="<?= htmlspecialchars($user['firstname'], ENT_QUOTES) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Last name</label>
                <input type="text" name="lastname" class="form-control" required
                       value="<?= htmlspecialchars($user['lastname'], ENT_QUOTES) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Identification number</label>
                <input type="text" name="ident_number" class="form-control"
                       value="<?= htmlspecialchars($user['ident_number'], ENT_QUOTES) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Mobile phone</label>
                <input type="text" name="mobile_phone" class="form-control" required
                       value="<?= htmlspecialchars($user['mobile_phone'], ENT_QUOTES) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required
                       value="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Profile photo <small class="text-muted">(saved in photos/ using phone number)</small></label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="form-control">
            </div>

            <?php if ($isAdmin) { ?>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="user" <?= ($user['role'] === 'user') ? 'selected' : '' ?>>user</option>
                        <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>admin</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="true" <?= $isUserActive ? 'selected' : '' ?>>Active</option>
                        <option value="false" <?= !$isUserActive ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            <?php } ?>

            <div class="mb-3">
                <label class="form-label">New password <small class="text-muted">(leave blank to keep current)</small></label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="<?= $isAdmin ? 'list_users.php' : 'home.php' ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>