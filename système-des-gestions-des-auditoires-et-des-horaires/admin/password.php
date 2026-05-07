<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();

$passwordError = '';
$isSuperAdmin = isSuperAdmin();
$permissionsMap = getPermissionLabels();
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($newPassword !== $confirmPassword) {
        $passwordError = 'La confirmation du nouveau mot de passe ne correspond pas.';
    } else {
        $result = updateAdminPassword($currentPassword, $newPassword);
        if ($result['success']) {
            flashMessage('Mot de passe mis a jour avec succes.');
            header('Location: ' . url('admin/password.php'));
            exit;
        }

        $passwordError = implode(' ', $result['errors']);
    }
}

if ($isSuperAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = cleanText((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $newAdminIsSuperAdmin = isset($_POST['is_super_admin']);
    $permissions = (array) ($_POST['permissions'] ?? []);

    $permissionValues = [];
    foreach (array_keys($permissionsMap) as $key) {
        $permissionValues[$key] = in_array($key, $permissions, true);
    }

    $result = addAdminAccount($username, $password, $permissionValues, $newAdminIsSuperAdmin);
    if ($result['success']) {
        flashMessage('Administrateur ajoute avec succes.');
    } else {
        flashMessage(implode(' ', $result['errors']), 'error');
    }

    header('Location: ' . url('admin/password.php'));
    exit;
}

if ($isSuperAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $originalUsername = cleanText((string) ($_POST['original_username'] ?? ''));
    $username = cleanText((string) ($_POST['username'] ?? ''));
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $isActive = isset($_POST['is_active']);
    $targetIsSuperAdmin = isset($_POST['is_super_admin']);
    $permissions = (array) ($_POST['permissions'] ?? []);

    $permissionValues = [];
    foreach (array_keys($permissionsMap) as $key) {
        $permissionValues[$key] = in_array($key, $permissions, true);
    }

    $result = updateAdminAccount($originalUsername !== '' ? $originalUsername : $username, $username, $permissionValues, $isActive, $newPassword !== '' ? $newPassword : null, $targetIsSuperAdmin);
    if ($result['success']) {
        flashMessage('Administrateur modifie avec succes.');
    } else {
        flashMessage(implode(' ', $result['errors']), 'error');
    }

    header('Location: ' . url('admin/password.php'));
    exit;
}

if ($isSuperAdmin && isset($_GET['edit'])) {
    $username = cleanText((string) $_GET['edit']);
    $admin = getAdminByUsername($username);
    if ($admin !== null) {
        $editing = $admin;
    }
}

$admins = $isSuperAdmin ? listAdmins() : [];

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card">
    <h2>Gestion des administrateurs</h2>
    <p>
        <?php if ($isSuperAdmin): ?>
            Ajoutez des administrateurs et attribuez des droits d'acces par module.
        <?php else: ?>
            Section reservee au super-administrateur.
        <?php endif; ?>
    </p>
</section>

<section class="card">
    <h3><?= $isSuperAdmin ? ($editing ? 'Modifier un administrateur' : 'Ajouter un administrateur') : 'Ajouter un administrateur' ?></h3>
    <?php if (!$isSuperAdmin): ?>
        <div class="blur-lock">
            <div class="blurred-content" aria-hidden="true">
                <form>
                    <div class="input-row">
                        <div>
                            <label>Nom d'utilisateur</label>
                            <input value="********" disabled>
                        </div>
                        <div>
                            <label>Mot de passe</label>
                            <input value="********" disabled>
                        </div>
                        <div>
                            <label>
                                <input type="checkbox" disabled> Super-administrateur
                            </label>
                        </div>
                    </div>

                    <div class="grid">
                        <?php foreach ($permissionsMap as $label): ?>
                            <label>
                                <input type="checkbox" checked disabled>
                                <?= htmlspecialchars($label) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button class="btn" type="button" disabled>Ajouter l'administrateur</button>
                </form>
            </div>
            <div class="blur-overlay">Seul le super-administrateur peut gerer les administrateurs.</div>
        </div>
    <?php elseif ($editing): ?>
        <form method="post">
            <input type="hidden" name="update_admin" value="1">
            <input type="hidden" name="original_username" value="<?= htmlspecialchars((string) ($editing['username'] ?? '')) ?>">
            <div class="input-row">
                <div>
                    <label for="username">Nom d'utilisateur</label>
                    <input id="username" name="username" required value="<?= htmlspecialchars((string) ($editing['username'] ?? '')) ?>">
                </div>
                <div>
                    <label for="new_password">Nouveau mot de passe (optionnel)</label>
                    <input id="new_password" name="new_password" type="password" minlength="6">
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?= !empty($editing['is_active']) ? 'checked' : '' ?>> Compte actif
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="is_super_admin" value="1" <?= !empty($editing['is_super_admin']) ? 'checked' : '' ?>> Super-administrateur
                    </label>
                </div>
            </div>

            <div class="grid">
                <?php foreach ($permissionsMap as $key => $label): ?>
                    <label>
                        <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($key) ?>" <?= !empty($editing['permissions'][$key]) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="actions">
                <button class="btn btn-warning" type="submit">Enregistrer</button>
                <a class="btn" href="<?= htmlspecialchars(url('admin/password.php')) ?>">Annuler</a>
            </div>
        </form>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="add_admin" value="1">
            <div class="input-row">
                <div>
                    <label for="username">Nom d'utilisateur</label>
                    <input id="username" name="username" required minlength="3">
                </div>
                <div>
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" required minlength="6">
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="is_super_admin" value="1"> Super-administrateur
                    </label>
                </div>
            </div>

            <div class="grid">
                <?php foreach ($permissionsMap as $key => $label): ?>
                    <label>
                        <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($key) ?>" checked>
                        <?= htmlspecialchars($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <button class="btn" type="submit">Ajouter l'administrateur</button>
        </form>
    <?php endif; ?>
</section>

<?php if ($isSuperAdmin): ?>
<section class="card table-wrap">
    <div class="table-tools">
        <h3>Liste des administrateurs</h3>
        <input class="table-filter" type="search" data-table-filter="#admins-table" placeholder="Rechercher un administrateur...">
    </div>
    <table id="admins-table">
        <thead>
        <tr>
            <th>Utilisateur</th>
            <th>Etat</th>
            <th>Role</th>
            <th>Droits</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($admins)): ?>
            <tr><td colspan="5">Aucun administrateur configure.</td></tr>
        <?php else: ?>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($admin['username'] ?? '')) ?></td>
                    <td><?= !empty($admin['is_active']) ? '<span class="badge">Actif</span>' : '<span class="badge">Inactif</span>' ?></td>
                    <td><?= !empty($admin['is_super_admin']) ? '<span class="badge">Super-admin</span>' : '<span class="badge">Admin</span>' ?></td>
                    <td>
                        <?php
                        $grants = [];
                        foreach ($permissionsMap as $key => $label) {
                            if (!empty($admin['permissions'][$key])) {
                                $grants[] = $label;
                            }
                        }
                        ?>
                        <?= htmlspecialchars(implode(', ', $grants)) ?>
                    </td>
                    <td>
                        <a class="btn btn-warning" href="<?= htmlspecialchars(url('admin/password.php?edit=' . urlencode((string) ($admin['username'] ?? '')))) ?>">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>

<section class="card auth-card" style="margin-top: 40px;">
    <h2>Changer le mot de passe</h2>
    <p>La mise a jour est immediate et utilise un hash PHP securise.</p>

    <?php if ($passwordError !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($passwordError) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
        <input type="hidden" name="update_password" value="1">
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
