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
$currentPhoto = !empty($user['url_photo']) ? $user['url_photo'] : 'photos/photo_default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isAdmin ? 'Edit User' : 'Edit Profile' ?> — MelanieViToM CustomGifts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Destiney&family=Muli:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-white: #FFFFFF;
            --sand: #CAAF83;
            --gold: #FFBD59;
            --caramel: #C17A35;
            --coffee-mid: #92521A;
            --coffee-dark: #5C3B2B;
            --ink-black: #000000;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background-color: var(--bg-white);
            color: var(--ink-black);
            font-family: 'Muli', Corbel, -apple-system, BlinkMacSystemFont, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Patrón geométrico de fondo */
        .brand-pattern-bg {
            position: fixed;
            inset: 0;
            background-image: url("images/PATRONES.png");
            background-repeat: repeat;
            background-size: 420px auto;
            opacity: 0.065;
            pointer-events: none;
            z-index: 0;
            animation: slowPatternDrift 36s linear infinite;
        }

        @keyframes slowPatternDrift {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 420px 210px;
            }
        }

        .content-layer {
            position: relative;
            z-index: 1;
        }

        /* Barra superior */
        .brand-navbar {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 2px solid var(--sand);
        }

        .ig-link-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: var(--ink-black);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            transition: transform 0.25s ease, color 0.25s ease;
        }

        .ig-link-brand:hover {
            color: var(--coffee-mid);
            transform: translateY(-1px);
        }

        .ig-icon-badge {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--caramel) 55%, var(--coffee-mid) 100%);
            color: var(--bg-white);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(146, 82, 26, 0.22);
        }

        .btn-outline-brand {
            color: var(--coffee-dark);
            border: 1.5px solid var(--coffee-dark);
            background-color: transparent;
            font-weight: 600;
            border-radius: 999px;
            padding: 0.42rem 1.2rem;
            transition: all 0.25s ease;
        }

        .btn-outline-brand:hover {
            background-color: var(--sand);
            border-color: var(--coffee-dark);
            color: var(--ink-black);
            transform: translateY(-1px);
        }

        .btn-primary-brand {
            background: linear-gradient(135deg, var(--gold) 0%, var(--caramel) 100%);
            color: var(--ink-black);
            border: none;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.52rem 1.35rem;
            box-shadow: 0 4px 14px rgba(146, 82, 26, 0.22);
            transition: all 0.25s ease;
        }

        .btn-primary-brand:hover {
            background: linear-gradient(135deg, var(--caramel) 0%, var(--coffee-mid) 100%);
            color: var(--bg-white);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(92, 59, 43, 0.3);
        }

        /* Tarjeta de edición */
        .edit-card {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.96);
            border: 1.5px solid var(--sand);
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px rgba(92, 59, 43, 0.11);
            overflow: hidden;
            animation: cardEnter 0.6s ease-out both;
        }

        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-ribbon-bar {
            height: 7px;
            background: linear-gradient(90deg, var(--sand), var(--gold), var(--caramel), var(--coffee-mid), var(--coffee-dark));
        }

        .page-title {
            font-family: 'Destiney', cursive;
            font-size: clamp(2.2rem, 4.5vw, 3rem);
            color: var(--ink-black);
            line-height: 1.1;
        }

        .page-subtitle {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--coffee-mid);
        }

        .avatar-preview {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--gold);
            box-shadow: 0 6px 16px rgba(92, 59, 43, 0.14);
            background-color: #FAF6F0;
        }

        .form-label-brand {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--coffee-dark);
            margin-bottom: 0.35rem;
        }

        .form-control-brand,
        .form-select-brand {
            border: 1.5px solid rgba(202, 175, 131, 0.9);
            border-radius: 0.75rem;
            padding: 0.62rem 0.9rem;
            background-color: var(--bg-white);
            color: var(--ink-black);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control-brand:focus,
        .form-select-brand:focus {
            border-color: var(--caramel);
            box-shadow: 0 0 0 0.22rem rgba(255, 189, 89, 0.32);
            outline: none;
        }

        .btn-toggle-pass {
            border: 1.5px solid rgba(202, 175, 131, 0.9);
            border-left: 0;
            border-radius: 0 0.75rem 0.75rem 0;
            background-color: #FAF6F0;
            color: var(--coffee-dark);
            padding: 0 0.85rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .btn-toggle-pass:hover {
            background-color: var(--sand);
            color: var(--ink-black);
        }

        .input-group .form-control-brand {
            border-right: 0;
        }

        /* Pie de página */
        .brand-footer {
            background-color: var(--coffee-dark);
            color: var(--bg-white);
            border-top: 3px solid var(--gold);
        }

        .brand-footer a {
            color: var(--gold);
            text-decoration: none;
        }

        .brand-footer a:hover {
            text-decoration: underline;
        }

        @media (prefers-reduced-motion: reduce) {
            .brand-pattern-bg,
            .edit-card {
                animation: none !important;
            }
        }
    </style>
</head>
<body class="d-flex flex-column justify-content-between">
    <!-- Patrón geométrico animado de fondo -->
    <div class="brand-pattern-bg" aria-hidden="true"></div>

    <!-- Barra superior -->
    <nav class="navbar brand-navbar sticky-top content-layer py-2">
        <div class="container d-flex align-items-center justify-content-between gap-2">
            <a href="https://www.instagram.com/melanievitom_customgifts/"
               target="_blank"
               rel="noopener noreferrer"
               class="ig-link-brand">
                <span class="ig-icon-badge" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                    </svg>
                </span>
                <span>@melanievitom_customgifts</span>
            </a>

            <div class="d-flex align-items-center gap-2">
                <a href="home.php" class="btn btn-outline-brand btn-sm">Home</a>
                <a href="logout.php" class="btn btn-primary-brand btn-sm py-1 px-3">Log out</a>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container my-4 my-md-5 d-flex justify-content-center align-items-center content-layer">
        <div class="edit-card">
            <div class="hero-ribbon-bar"></div>
            <div class="p-4 p-md-5">
                <div class="text-center mb-4">
                    <img src="<?= htmlspecialchars($currentPhoto, ENT_QUOTES) ?>"
                         alt="User profile photo"
                         class="avatar-preview mb-3"
                         onerror="this.src='photos/photo_default.png';">
                    <div class="page-subtitle mb-1">
                        <?= $isAdmin ? 'User #' . (int)$user['id'] : 'Account Settings' ?>
                    </div>
                    <h1 class="page-title mb-0">
                        <?= $isAdmin ? 'Edit User' : 'Edit My Profile' ?>
                    </h1>
                </div>

                <form method="POST" action="update_user.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="firstname" class="form-label-brand">First name</label>
                            <input type="text"
                                   id="firstname"
                                   name="firstname"
                                   class="form-control form-control-brand"
                                   required
                                   value="<?= htmlspecialchars($user['firstname'] ?? '', ENT_QUOTES) ?>">
                        </div>

                        <div class="col-sm-6">
                            <label for="lastname" class="form-label-brand">Last name</label>
                            <input type="text"
                                   id="lastname"
                                   name="lastname"
                                   class="form-control form-control-brand"
                                   required
                                   value="<?= htmlspecialchars($user['lastname'] ?? '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="ident_number" class="form-label-brand">Identification number</label>
                            <input type="text"
                                   id="ident_number"
                                   name="ident_number"
                                   class="form-control form-control-brand"
                                   value="<?= htmlspecialchars($user['ident_number'] ?? '', ENT_QUOTES) ?>">
                        </div>

                        <div class="col-sm-6">
                            <label for="mobile_phone" class="form-label-brand">Mobile phone</label>
                            <input type="tel"
                                   id="mobile_phone"
                                   name="mobile_phone"
                                   class="form-control form-control-brand"
                                   required
                                   value="<?= htmlspecialchars($user['mobile_phone'] ?? '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label-brand">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control form-control-brand"
                               required
                               value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="photo" class="form-label-brand">
                            Profile photo <small class="text-muted fw-normal">(saved in photos/ by phone number)</small>
                        </label>
                        <input type="file"
                               id="photo"
                               name="photo"
                               accept="image/jpeg,image/png,image/webp"
                               class="form-control form-control-brand">
                    </div>

                    <?php if ($isAdmin) { ?>
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="role" class="form-label-brand">Role</label>
                                <select id="role" name="role" class="form-select form-select-brand">
                                    <option value="user" <?= ($user['role'] === 'user') ? 'selected' : '' ?>>user</option>
                                    <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>admin</option>
                                </select>
                            </div>

                            <div class="col-sm-6">
                                <label for="status" class="form-label-brand">Status</label>
                                <select id="status" name="status" class="form-select form-select-brand">
                                    <option value="true" <?= $isUserActive ? 'selected' : '' ?>>Active</option>
                                    <option value="false" <?= !$isUserActive ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="mb-4">
                        <label for="password" class="form-label-brand">
                            New password <small class="text-muted fw-normal">(leave blank to keep current)</small>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control form-control-brand"
                                   placeholder="Optional new password"
                                   autocomplete="new-password">
                            <button type="button"
                                    class="btn btn-toggle-pass"
                                    id="togglePassword"
                                    aria-label="Show or hide password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button type="submit" class="btn btn-primary-brand flex-grow-1">Save changes</button>
                        <a href="<?= $isAdmin ? 'list_users.php' : 'home.php' ?>" class="btn btn-outline-brand px-4">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Pie de página -->
    <footer class="brand-footer py-3 content-layer">
        <div class="container d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 small">
            <div>
                By <a href="https://www.instagram.com/melanievitom" target="_blank" rel="noopener noreferrer">@melanievitom</a>
            </div>
            <div class="d-inline-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" style="color: var(--gold);">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                </svg>
                <span>melanievitom@gmail.com</span>
            </div>
        </div>
    </footer>

    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
        });
    </script>
</body>
</html>