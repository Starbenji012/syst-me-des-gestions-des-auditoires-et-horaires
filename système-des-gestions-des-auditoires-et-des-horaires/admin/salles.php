<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requirePermission('manage_rooms');

$isSuperAdmin = isSuperAdmin();

$salles = readJson('salles.json');

$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_salle'])) {
    if (!$isSuperAdmin) {
        flashMessage('Acces refuse: reserve au super-administrateur.', 'error');
        header('Location: ' . url('admin/salles.php'));
        exit;
    }

    $id = cleanText((string) ($_POST['id'] ?? ''));
    $designation = cleanText((string) ($_POST['designation'] ?? ''));
    $capacite = cleanInt($_POST['capacite'] ?? 0);

    $index = findIndexByKey($salles, 'id', $id);
    if ($id === '' || $designation === '' || $capacite <= 0) {
        flashMessage('Donnees invalides pour la modification de la salle.', 'error');
    } elseif ($index < 0) {
        flashMessage('Salle introuvable pour modification.', 'error');
    } else {
        $salles[$index]['designation'] = $designation;
        $salles[$index]['capacite'] = $capacite;
        writeJson('salles.json', $salles);
        flashMessage('Salle modifiee avec succes.');
    }

    header('Location: ' . url('admin/salles.php'));
    exit;
}

if (isset($_GET['delete'])) {
    if (!$isSuperAdmin) {
        flashMessage('Acces refuse: reserve au super-administrateur.', 'error');
        header('Location: ' . url('admin/salles.php'));
        exit;
    }

    $id = cleanText((string) $_GET['delete']);
    $index = findIndexByKey($salles, 'id', $id);

    if ($index >= 0) {
        array_splice($salles, $index, 1);
        writeJson('salles.json', $salles);
        flashMessage('Salle supprimee avec succes.');
    } else {
        flashMessage('Salle introuvable.', 'error');
    }

    header('Location: ' . url('admin/salles.php'));
    exit;
}

if (isset($_GET['edit'])) {
    $id = cleanText((string) $_GET['edit']);
    $index = findIndexByKey($salles, 'id', $id);
    if ($index >= 0) {
        $editing = $salles[$index];
    }
}

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card">
    <h2>Gestion des salles</h2>
    <?php if (!$isSuperAdmin): ?>
        <div class="alert alert-error">Seul le super-administrateur peut ajouter, modifier ou supprimer des salles.</div>
    <?php endif; ?>
    <?php if ($editing): ?>
        <?php if ($isSuperAdmin): ?>
        <form method="post">
            <input type="hidden" name="update_salle" value="1">
            <div class="input-row">
                <div>
                    <label for="id">ID</label>
                    <input id="id" name="id" required readonly value="<?= htmlspecialchars((string) ($editing['id'] ?? '')) ?>">
                </div>
                <div>
                    <label for="designation">Designation</label>
                    <input id="designation" name="designation" required maxlength="100" value="<?= htmlspecialchars((string) ($editing['designation'] ?? '')) ?>">
                </div>
                <div>
                    <label for="capacite">Capacite</label>
                    <input id="capacite" name="capacite" type="number" min="1" required value="<?= (int) ($editing['capacite'] ?? 0) ?>">
                </div>
            </div>
            <div class="actions">
                <button class="btn btn-warning" type="submit">Enregistrer la modification</button>
                <a class="btn" href="<?= htmlspecialchars(url('admin/salles.php')) ?>">Annuler</a>
            </div>
        </form>
        <?php else: ?>
            <p>Mode lecture seule pour ce compte.</p>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($isSuperAdmin): ?>
        <form method="post" action="<?= htmlspecialchars(url('actions/add_salle.php')) ?>">
            <div class="input-row">
                <div>
                    <label for="id">ID</label>
                    <input id="id" name="id" required maxlength="20" placeholder="Ex: S5">
                </div>
                <div>
                    <label for="designation">Designation</label>
                    <input id="designation" name="designation" required maxlength="100" placeholder="Ex: Amphitheatre E">
                </div>
                <div>
                    <label for="capacite">Capacite</label>
                    <input id="capacite" name="capacite" type="number" min="1" required>
                </div>
            </div>
            <button class="btn" type="submit">Ajouter la salle</button>
        </form>
        <?php else: ?>
            <p>Mode lecture seule pour ce compte.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="card table-wrap">
    <h3>Liste des salles</h3>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Designation</th>
            <th>Capacite</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($salles as $salle): ?>
            <tr>
                <td><?= htmlspecialchars((string) ($salle['id'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($salle['designation'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($salle['capacite'] ?? 0)) ?></td>
                <td>
                    <?php if ($isSuperAdmin): ?>
                        <a class="btn btn-warning" href="<?= htmlspecialchars(url('admin/salles.php?edit=' . urlencode((string) ($salle['id'] ?? '')))) ?>">Modifier</a>
                        <a class="btn btn-danger" href="<?= htmlspecialchars(url('admin/salles.php?delete=' . urlencode((string) ($salle['id'] ?? '')))) ?>" onclick="return confirm('Supprimer cette salle ?')">Supprimer</a>
                    <?php else: ?>
                        <span class="badge">Lecture seule</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
