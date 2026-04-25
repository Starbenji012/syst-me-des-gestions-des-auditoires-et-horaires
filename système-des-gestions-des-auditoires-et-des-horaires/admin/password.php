<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($newPassword !== $confirmPassword) {
        $error = 'La confirmation du nouveau mot de passe ne correspond pas.';
    } else {
        $result = updateAdminPassword($currentPassword, $newPassword);
        if ($result['success']) {
            flashMessage('Mot de passe mis a jour avec succes.');
            header('Location: ' . url('admin/dashboard.php'));
            exit;
        }

        $error = implode(' ', $result['errors']);
    }
}

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card auth-card">
    <h2>Changer le mot de passe</h2>
    <p>La mise a jour est immediate et utilise un hash PHP securise.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
        <div>
            <label for="current_password">Mot de passe actuel</label>
            <input id="current_password" name="current_password" type="password" required>
        </div>
        <div>
            <label for="new_password">Nouveau mot de passe</label>
            <input id="new_password" name="new_password" type="password" required minlength="6">
        </div>
        <div>
            <label for="confirm_password">Confirmation</label>
            <input id="confirm_password" name="confirm_password" type="password" required minlength="6">
        </div>
        <button class="btn" type="submit">Mettre a jour</button>
    </form>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
