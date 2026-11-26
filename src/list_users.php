<?php
/*
    Use this URL to print data in browser:
    http://localhost/main-store/src/list_users.php
    */
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: home.php');
    exit();
}

include('../settings/database.php');

$sessionUserId = (int)$_SESSION['user_id'];

$sql = "
    SELECT id, firstname, lastname, ident_number, mobile_phone, email, role, status, url_photo
    FROM users
    WHERE deleted_at IS NULL
    ORDER BY id ASC
";
$result = pg_query_params($local_conn, $sql, []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management — MelanieViToM CustomGifts</title>
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
            padding: 0.42rem 1.15rem;
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
            padding: 0.45rem 1.25rem;
            box-shadow: 0 4px 14px rgba(146, 82, 26, 0.22);
            transition: all 0.25s ease;
        }

        .btn-primary-brand:hover {
            background: linear-gradient(135deg, var(--caramel) 0%, var(--coffee-mid) 100%);
            color: var(--bg-white);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(92, 59, 43, 0.3);
        }

        /* Tarjeta principal */
        .admin-card {
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
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--coffee-mid);
        }

        .table-brand {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table-brand thead th {
            background-color: #FAF6F0;
            color: var(--coffee-dark);
            font-weight: 700;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 2px solid var(--sand);
            white-space: nowrap;
        }

        .table-brand tbody tr {
            transition: background-color 0.2s ease;
        }

        .table-brand tbody tr:hover {
            background-color: rgba(255, 189, 89, 0.12);
        }

        .user-thumb {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--sand);
            background-color: #FAF6F0;
        }

        .badge-role-admin {
            background-color: var(--coffee-dark);
            color: var(--bg-white);
            font-weight: 700;
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            font-size: 0.76rem;
        }

        .badge-role-user {
            background-color: rgba(202, 175, 131, 0.35);
            color: var(--coffee-dark);
            border: 1px solid var(--sand);
            font-weight: 700;
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            font-size: 0.76rem;
        }

        .badge-status-active {
            background-color: rgba(255, 189, 89, 0.32);
            color: var(--coffee-dark);
            border: 1px solid var(--caramel);
            font-weight: 700;
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            font-size: 0.76rem;
        }

        .badge-status-inactive {
            background-color: #F3EFEA;
            color: #6C5A4F;
            border: 1px solid #D5C7B8;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            font-size: 0.76rem;
        }

        .btn-action-edit {
            border: 1.5px solid var(--caramel);
            color: var(--coffee-dark);
            background-color: transparent;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            font-size: 0.78rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-action-edit:hover {
            background-color: var(--gold);
            border-color: var(--caramel);
            color: var(--ink-black);
        }

        .btn-action-status {
            border: 1.5px solid var(--coffee-mid);
            color: var(--coffee-mid);
            background-color: transparent;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            font-size: 0.78rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-action-status:hover {
            background-color: var(--coffee-mid);
            color: var(--bg-white);
        }

        .btn-action-delete {
            border: 1.5px solid #8B261D;
            color: #8B261D;
            background-color: transparent;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.28rem 0.75rem;
            font-size: 0.78rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-action-delete:hover {
            background-color: #8B261D;
            color: var(--bg-white);
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
            .admin-card {
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
                <a href="logout.php" class="btn btn-primary-brand btn-sm py-1 px-3">Log out</a>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container my-4 my-md-5 content-layer">
        <div class="admin-card">
            <div class="hero-ribbon-bar"></div>
            <div class="p-4 p-md-5">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                    <div>
                        <div class="page-subtitle mb-1">Admin Panel</div>
                        <h1 class="page-title mb-0">Users Management</h1>
                    </div>
                    <a href="home.php" class="btn btn-outline-brand btn-sm align-self-start align-self-md-center">
                        Back to Home
                    </a>
                </div>

                <?php if (!$result) { ?>
                    <div class="alert alert-danger rounded-4 mb-0">
                        Error loading users: <?= htmlspecialchars(pg_last_error($local_conn), ENT_QUOTES) ?>
                    </div>
                <?php } elseif (pg_num_rows($result) === 0) { ?>
                    <div class="text-center py-4 text-secondary">
                        No registered users found.
                    </div>
                <?php } else { ?>
                    <div class="table-responsive rounded-4 border" style="border-color: rgba(202, 175, 131, 0.75) !important;">
                        <table class="table table-brand table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Photo</th>
                                    <th>Full name</th>
                                    <th>ID number</th>
                                    <th>Mobile phone</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = pg_fetch_assoc($result)) {
                                    $rowId = (int)$row['id'];
                                    $fullName = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
                                    $rowRole = ($row['role'] === 'admin') ? 'admin' : 'user';
                                    $isActive = ($row['status'] === 't' || $row['status'] === true || $row['status'] === 'true');
                                    $statusLabel = $isActive ? 'Active' : 'Inactive';
                                    $photoSrc = !empty($row['url_photo']) ? $row['url_photo'] : 'photos/photo_default.png';
                                ?>
                                    <tr>
                                        <td><strong>#<?= $rowId ?></strong></td>
                                        <td>
                                            <img src="<?= htmlspecialchars($photoSrc, ENT_QUOTES) ?>"
                                                 alt="<?= htmlspecialchars($fullName, ENT_QUOTES) ?>"
                                                 class="user-thumb"
                                                 onerror="this.src='photos/photo_default.png';">
                                        </td>
                                        <td class="fw-semibold"><?= htmlspecialchars($fullName, ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($row['ident_number'] ?? '', ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($row['mobile_phone'] ?? '', ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES) ?></td>
                                        <td>
                                            <span class="<?= $rowRole === 'admin' ? 'badge-role-admin' : 'badge-role-user' ?>">
                                                <?= htmlspecialchars($rowRole, ENT_QUOTES) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?= $isActive ? 'badge-status-active' : 'badge-status-inactive' ?>">
                                                <?= $statusLabel ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                                <a href="edit_user.php?id=<?= $rowId ?>" class="btn-action-edit">
                                                    Edit
                                                </a>

                                                <?php if ($rowId !== $sessionUserId) { ?>
                                                    <a href="update_user_status.php?userID=<?= $rowId ?>&userStatus=<?= urlencode($statusLabel) ?>"
                                                       class="btn-action-status">
                                                        <?= $isActive ? 'Deactivate' : 'Activate' ?>
                                                    </a>

                                                    <a href="delete_user.php?userID=<?= $rowId ?>"
                                                       class="btn-action-delete"
                                                       onclick="return confirm('Are you sure you want to delete this user?');">
                                                        Delete
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
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
</body>
</html>