<?php
// Iniciar la sesión para poder acceder a las variables almacenadas
    session_start();

    // Verificar si el usuario ha iniciado sesión; si no, redirigirlo al login
    if (!isset($_SESSION['user_id'])) {
        header('Location: signin.html');
        exit();
    }

$role = $_SESSION['user_role'] ?? 'user';
$isAdmin = ($role === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?= htmlspecialchars($_SESSION['user_photo'] ?? 'photos/photo_default.png', ENT_QUOTES) ?>" width="48" alt="Avatar">
        <div>
            <h1 class="h3 mb-0">Welcome, <?= htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'], ENT_QUOTES) ?>!</h1>
            <small class="text-muted">Role: <strong><?= htmlspecialchars($role, ENT_QUOTES) ?></strong></small>
        </div>
    </div>

    <?php if ($isAdmin) { ?>
        <div class="mt-3">
            <a href="list_users.php" class="btn btn-primary">Manage users</a>
        </div>
    <?php } ?>

    <div class="mt-4">
        <a href="edit_user.php?id=<?= (int)$_SESSION['user_id'] ?>" class="btn btn-outline-primary">Edit my profile</a>
        <a href="logout.php" class="btn btn-outline-secondary">Log out</a>
    </div>
</body>
</html>