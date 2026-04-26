<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isAdminLoggedIn()) {
    header('Location: ' . url('admin/password.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanText((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (adminLogin($username, $password)) {
        flashMessage('Connexion administrateur reussie.');
        header('Location: ' . url('admin/password.php'));
        exit;
    }

    $error = 'Identifiants invalides.';
}

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card auth-card">
    <h2>Connexion administrateur</h2>
    <p>Connectez-vous pour gerer les salles, cours et le planning.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
        <div>
            <label for="username">Nom d'utilisateur</label>
            <input id="username" name="username" required>
        </div>
        <div>
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" required>
        </div>
        <button class="btn" type="submit">Se connecter</button>
    </form>

</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
