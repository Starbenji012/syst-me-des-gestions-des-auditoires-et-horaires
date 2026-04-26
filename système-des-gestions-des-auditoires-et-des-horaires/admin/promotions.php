<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requirePermission('manage_promotions');

$isSuperAdmin = isSuperAdmin();

$promotions = readJson('promotions.json');

$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_promotion'])) {
    if (!$isSuperAdmin) {
        flashMessage('Acces refuse: reserve au super-administrateur.', 'error');
        header('Location: ' . url('admin/promotions.php'));
        exit;
    }

    $id = cleanText((string) ($_POST['id_promotion'] ?? ''));
    $libelle = cleanText((string) ($_POST['libelle'] ?? ''));
    $effectif = cleanInt($_POST['effectif_total'] ?? 0);

    if ($id === '' || $libelle === '' || $effectif <= 0) {
        flashMessage('Donnees invalides pour la promotion.', 'error');
    } elseif (findIndexByKey($promotions, 'id_promotion', $id) >= 0) {
        flashMessage('ID promotion deja utilise.', 'error');
    } else {
        $promotions[] = [
            'id_promotion' => $id,
            'libelle' => $libelle,
            'effectif_total' => $effectif,
        ];

        writeJson('promotions.json', $promotions);
        flashMessage('Promotion ajoutee avec succes.');
    }

    header('Location: ' . url('admin/promotions.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_promotion'])) {
    if (!$isSuperAdmin) {
        flashMessage('Acces refuse: reserve au super-administrateur.', 'error');
        header('Location: ' . url('admin/promotions.php'));
        exit;
    }

    $id = cleanText((string) ($_POST['id_promotion'] ?? ''));
    $libelle = cleanText((string) ($_POST['libelle'] ?? ''));
    $effectif = cleanInt($_POST['effectif_total'] ?? 0);

    $index = findIndexByKey($promotions, 'id_promotion', $id);

    if ($id === '' || $libelle === '' || $effectif <= 0) {
        flashMessage('Donnees invalides pour la modification.', 'error');
    } elseif ($index < 0) {
        flashMessage('Promotion introuvable.', 'error');
    } else {
        $promotions[$index]['libelle'] = $libelle;
        $promotions[$index]['effectif_total'] = $effectif;
        writeJson('promotions.json', $promotions);
        flashMessage('Promotion modifiee avec succes.');
    }

    header('Location: ' . url('admin/promotions.php'));
    exit;
}

if (isset($_GET['delete'])) {
    if (!$isSuperAdmin) {
        flashMessage('Acces refuse: reserve au super-administrateur.', 'error');
        header('Location: ' . url('admin/promotions.php'));
        exit;
    }

    $id = cleanText((string) $_GET['delete']);
    $index = findIndexByKey($promotions, 'id_promotion', $id);

    if ($index >= 0) {
        array_splice($promotions, $index, 1);
        writeJson('promotions.json', $promotions);
        flashMessage('Promotion supprimee avec succes.');
    } else {
        flashMessage('Promotion introuvable.', 'error');
    }

    header('Location: ' . url('admin/promotions.php'));
    exit;
}

if (isset($_GET['edit'])) {
    $id = cleanText((string) $_GET['edit']);
    $index = findIndexByKey($promotions, 'id_promotion', $id);
    if ($index >= 0) {
        $editing = $promotions[$index];
    }
}

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card">
    <h2>Gestion des promotions</h2>
    <?php if (!$isSuperAdmin): ?>
        <div class="alert alert-error">Seul le super-administrateur peut ajouter, modifier ou supprimer des promotions.</div>
    <?php endif; ?>
    <?php if ($editing): ?>
        <?php if ($isSuperAdmin): ?>
        <form method="post">
            <input type="hidden" name="update_promotion" value="1">
            <div class="input-row">
                <div>
                    <label for="id_promotion">ID Promotion</label>
                    <input id="id_promotion" name="id_promotion" required readonly value="<?= htmlspecialchars((string) ($editing['id_promotion'] ?? '')) ?>">
                </div>
                <div>
                    <label for="libelle">Libelle</label>
                    <input id="libelle" name="libelle" required maxlength="80" value="<?= htmlspecialchars((string) ($editing['libelle'] ?? '')) ?>">
                </div>
                <div>
                    <label for="effectif_total">Effectif total</label>
                    <input id="effectif_total" name="effectif_total" type="number" min="1" required value="<?= (int) ($editing['effectif_total'] ?? 0) ?>">
                </div>
            </div>
            <div class="actions">
                <button class="btn btn-warning" type="submit">Enregistrer la modification</button>
                <a class="btn" href="<?= htmlspecialchars(url('admin/promotions.php')) ?>">Annuler</a>
            </div>
        </form>
        <?php else: ?>
            <p>Mode lecture seule pour ce compte.</p>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($isSuperAdmin): ?>
        <form method="post">
            <input type="hidden" name="add_promotion" value="1">
            <div class="input-row">
                <div>
                    <label for="id_promotion">ID Promotion</label>
                    <input id="id_promotion" name="id_promotion" required maxlength="20" placeholder="Ex: P3">
                </div>
                <div>
                    <label for="libelle">Libelle</label>
                    <input id="libelle" name="libelle" required maxlength="80">
                </div>
                <div>
                    <label for="effectif_total">Effectif total</label>
                    <input id="effectif_total" name="effectif_total" type="number" min="1" required>
                </div>
            </div>
            <button class="btn" type="submit">Ajouter la promotion</button>
        </form>
        <?php else: ?>
            <p>Mode lecture seule pour ce compte.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="card table-wrap">
    <h3>Liste des promotions</h3>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Libelle</th>
            <th>Effectif</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($promotions as $promotion): ?>
            <tr>
                <td><?= htmlspecialchars((string) ($promotion['id_promotion'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($promotion['libelle'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($promotion['effectif_total'] ?? 0)) ?></td>
                <td>
                    <?php if ($isSuperAdmin): ?>
                        <a class="btn btn-warning" href="<?= htmlspecialchars(url('admin/promotions.php?edit=' . urlencode((string) ($promotion['id_promotion'] ?? '')))) ?>">Modifier</a>
                        <a class="btn btn-danger" href="<?= htmlspecialchars(url('admin/promotions.php?delete=' . urlencode((string) ($promotion['id_promotion'] ?? '')))) ?>" onclick="return confirm('Supprimer cette promotion ?')">Supprimer</a>
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
