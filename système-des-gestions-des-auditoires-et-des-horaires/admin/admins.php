<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requirePermission('manage_admins');

$permissionsMap = getPermissionLabels();
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = cleanText((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $permissions = (array) ($_POST['permissions'] ?? []);

    $permissionValues = [];
    foreach (array_keys($permissionsMap) as $key) {
        $permissionValues[$key] = in_array($key, $permissions, true);
    }

    $result = addAdminAccount($username, $password, $permissionValues);
    if ($result['success']) {
        flashMessage('Administrateur ajoute avec succes.');
    } else {
        flashMessage(implode(' ', $result['errors']), 'error');
    }

    header('Location: ' . url('admin/admins.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $username = cleanText((string) ($_POST['username'] ?? ''));
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $isActive = isset($_POST['is_active']);
    $permissions = (array) ($_POST['permissions'] ?? []);

    $permissionValues = [];
    foreach (array_keys($permissionsMap) as $key) {
        $permissionValues[$key] = in_array($key, $permissions, true);
    }

    $result = updateAdminAccount($username, $permissionValues, $isActive, $newPassword !== '' ? $newPassword : null);
    if ($result['success']) {
        flashMessage('Administrateur modifie avec succes.');
    } else {
        flashMessage(implode(' ', $result['errors']), 'error');
    }

    header('Location: ' . url('admin/admins.php'));
    exit;
}

if (isset($_GET['edit'])) {
    $username = cleanText((string) $_GET['edit']);
    $admin = getAdminByUsername($username);
    if ($admin !== null) {
        $editing = $admin;
    }
}

$admins = listAdmins();

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card">
    <h2>Gestion des administrateurs</h2>
    <p>Ajoutez des administrateurs et attribuez des droits d'acces par module.</p>
</section>

<section class="card">
    <h3><?= $editing ? 'Modifier un administrateur' : 'Ajouter un administrateur' ?></h3>
    <?php if ($editing): ?>
        <form method="post">
            <input type="hidden" name="update_admin" value="1">
            <div class="input-row">
                <div>
                    <label for="username">Nom d'utilisateur</label>
                    <input id="username" name="username" readonly required value="<?= htmlspecialchars((string) ($editing['username'] ?? '')) ?>">
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
                <a class="btn" href="<?= htmlspecialchars(url('admin/admins.php')) ?>">Annuler</a>
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

<section class="card table-wrap">
    <h3>Liste des administrateurs</h3>
    <table>
        <thead>
        <tr>
            <th>Utilisateur</th>
            <th>Etat</th>
            <th>Droits</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($admins)): ?>
            <tr><td colspan="4">Aucun administrateur configure.</td></tr>
        <?php else: ?>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($admin['username'] ?? '')) ?></td>
                    <td><?= !empty($admin['is_active']) ? '<span class="badge">Actif</span>' : '<span class="badge">Inactif</span>' ?></td>
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
                        <a class="btn btn-warning" href="<?= htmlspecialchars(url('admin/admins.php?edit=' . urlencode((string) ($admin['username'] ?? '')))) ?>">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
