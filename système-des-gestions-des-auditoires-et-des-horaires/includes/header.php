<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$flash = consumeFlash();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$loggedIn = isAdminLoggedIn();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/css/style.php')) ?>">
    <script defer src="<?= htmlspecialchars(url('assets/js/app.js')) ?>"></script>
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <h1 class="logo"><?= htmlspecialchars(APP_NAME) ?></h1>
        <nav class="menu">
            <a class="<?= str_ends_with($currentPath, '/index.php') || $currentPath === url('') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('index.php')) ?>">Accueil</a>
            <a class="<?= str_contains($currentPath, '/pages/stats.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('pages/stats.php')) ?>">Statistiques</a>
            <?php if ($loggedIn): ?>
                <a class="<?= str_contains($currentPath, '/admin/dashboard.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('admin/dashboard.php')) ?>">Dashboard</a>
                <?php if (adminHasPermission('manage_planning')): ?>
                    <a class="<?= str_contains($currentPath, '/admin/planning.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('admin/planning.php')) ?>">Planning</a>
                <?php endif; ?>
                <?php if (adminHasPermission('manage_rooms')): ?>
                    <a class="<?= str_contains($currentPath, '/admin/salles.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('admin/salles.php')) ?>">Salles</a>
                <?php endif; ?>
                <?php if (adminHasPermission('manage_courses')): ?>
                    <a class="<?= str_contains($currentPath, '/admin/cours.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('admin/cours.php')) ?>">Cours</a>
                <?php endif; ?>
                <?php if (adminHasPermission('manage_promotions')): ?>
                    <a class="<?= str_contains($currentPath, '/admin/promotions.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('admin/promotions.php')) ?>">Promotions</a>
                <?php endif; ?>
                <?php if (adminHasPermission('manage_options')): ?>
                    <a class="<?= str_contains($currentPath, '/admin/options.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('admin/options.php')) ?>">Options</a>
                <?php endif; ?>
                <?php if (adminHasPermission('manage_admins')): ?>
                    <a class="<?= str_contains($currentPath, '/admin/admins.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('admin/admins.php')) ?>">Administrateurs</a>
                <?php endif; ?>
                <a class="<?= str_contains($currentPath, '/admin/password.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('admin/password.php')) ?>">Mot de passe</a>
                <a href="<?= htmlspecialchars(url('auth/logout.php')) ?>">Deconnexion</a>
            <?php else: ?>
                <a class="<?= str_contains($currentPath, '/auth/login.php') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('auth/login.php')) ?>">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars((string) ($flash['type'] ?? 'success')) ?>">
            <?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
        </div>
    <?php endif; ?>
