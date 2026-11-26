<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.html');
    exit();
}

include('../settings/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit();
}

$sessionUserId = (int)$_SESSION['user_id'];
$sessionRole = $_SESSION['user_role'] ?? 'user';
$isAdmin = ($sessionRole === 'admin');

$targetId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: $sessionUserId;

// Un usuario normal solo puede editar su propio registro
if (!$isAdmin && $targetId !== $sessionUserId) {
    header('Location: home.php');
    exit();
}

// Obtener registro actual para conservar url_photo, role o status si no aplica cambiarlos
$sqlCurrent = "
    SELECT id, url_photo, role, status
    FROM users
    WHERE id = $1 AND deleted_at IS NULL
    LIMIT 1
";
$resCurrent = pg_query_params($local_conn, $sqlCurrent, [$targetId]);

if (!$resCurrent || pg_num_rows($resCurrent) === 0) {
    $redirectBack = $isAdmin ? 'list_users.php' : 'home.php';
    echo "<script>alert('User not found.'); window.location='{$redirectBack}';</script>";
    exit();
}

$currentUser = pg_fetch_assoc($resCurrent);

$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$ident_number = trim($_POST['ident_number'] ?? '');
$mobile_phone = trim($_POST['mobile_phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$newPassword = $_POST['password'] ?? '';

// Solo el admin puede modificar role y status; un usuario normal conserva los suyos
if ($isAdmin) {
    $role = in_array($_POST['role'] ?? '', ['admin', 'user'], true) ? $_POST['role'] : $currentUser['role'];
    $status = (($_POST['status'] ?? '') === 'true') ? 'true' : 'false';
} else {
    $role = $currentUser['role'];
    $status = ($currentUser['status'] === 't' || $currentUser['status'] === true || $currentUser['status'] === 'true') ? 'true' : 'false';
}

$photoUrl = $currentUser['url_photo'] ?: 'photos/photo_default.png';
$redirectAfter = $isAdmin ? 'list_users.php' : 'edit_user.php?id=' . $targetId;

// Procesar carga de foto en src/photos/<mobile_phone>.<ext>
if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Error uploading photo.'); window.location='{$redirectAfter}';</script>";
        exit();
    }

    $tmpPath = $_FILES['photo']['tmp_name'];
    $imgInfo = @getimagesize($tmpPath);
    if ($imgInfo === false) {
        echo "<script>alert('The selected file is not a valid image.'); window.location='{$redirectAfter}';</script>";
        exit();
    }

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    $mime = $imgInfo['mime'] ?? '';
    if (!isset($allowedMimes[$mime])) {
        echo "<script>alert('Only JPG, PNG, or WEBP images are allowed.'); window.location='{$redirectAfter}';</script>";
        exit();
    }

    $phoneName = preg_replace('/[^0-9]/', '', $mobile_phone);
    if ($phoneName === '') {
        $phoneName = 'user_' . $targetId;
    }

    $ext = $allowedMimes[$mime];
    $photosDir = __DIR__ . '/photos';
    $fileName = $phoneName . '.' . $ext;
    $destPath = $photosDir . '/' . $fileName;

    if (!move_uploaded_file($tmpPath, $destPath)) {
        echo "<script>alert('Could not save photo in src/photos.'); window.location='{$redirectAfter}';</script>";
        exit();
    }

    $photoUrl = 'photos/' . $fileName;
}

if ($newPassword !== '') {
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $sqlUpdate = "
        UPDATE users
        SET
            firstname = $1,
            lastname = $2,
            ident_number = $3,
            mobile_phone = $4,
            email = $5,
            role = $6,
            status = $7,
            url_photo = $8,
            password = $9,
            updated_at = NOW()
        WHERE id = $10 AND deleted_at IS NULL
    ";
    $params = [
        $firstname,
        $lastname,
        $ident_number,
        $mobile_phone,
        $email,
        $role,
        $status,
        $photoUrl,
        $passwordHash,
        $targetId
    ];
} else {
    $sqlUpdate = "
        UPDATE users
        SET
            firstname = $1,
            lastname = $2,
            ident_number = $3,
            mobile_phone = $4,
            email = $5,
            role = $6,
            status = $7,
            url_photo = $8,
            updated_at = NOW()
        WHERE id = $9 AND deleted_at IS NULL
    ";
    $params = [
        $firstname,
        $lastname,
        $ident_number,
        $mobile_phone,
        $email,
        $role,
        $status,
        $photoUrl,
        $targetId
    ];
}

$resUpdate = pg_query_params($local_conn, $sqlUpdate, $params);

if ($resUpdate) {
    if ($targetId === $sessionUserId) {
        $_SESSION['user_firstname'] = $firstname;
        $_SESSION['user_lastname'] = $lastname;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_photo'] = $photoUrl;
    }
    echo "<script>alert('User updated successfully.'); window.location='{$redirectAfter}';</script>";
    exit();
} else {
    $err = htmlspecialchars(pg_last_error($local_conn), ENT_QUOTES);
    echo "<script>alert('Error updating user: {$err}'); window.location='{$redirectAfter}';</script>";
    exit();
}
?>