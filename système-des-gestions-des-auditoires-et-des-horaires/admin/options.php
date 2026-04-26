<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requirePermission('manage_options');

$isSuperAdmin = isSuperAdmin();

$options = readJson('options.json');
$promotions = readJson('promotions.json');

$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {
    if (!$isSuperAdmin) {
        flashMessage('Acces refuse: reserve au super-administrateur.', 'error');
        header('Location: ' . url('admin/options.php'));
        exit;
    }

    $id = cleanText((string) ($_POST['id_option'] ?? ''));
    $libelle = cleanText((string) ($_POST['libelle'] ?? ''));
    $promotionParente = cleanText((string) ($_POST['promotion_parente'] ?? ''));
    $effectif = cleanInt($_POST['effectif'] ?? 0);

    if ($id === '' || $libelle === '' || $promotionParente === '' || $effectif <= 0) {
        flashMessage('Donnees invalides pour l\'option.', 'error');
    } elseif (findIndexByKey($options, 'id_option', $id) >= 0) {
        flashMessage('ID option deja utilise.', 'error');
    } elseif (findIndexByKey($promotions, 'id_promotion', $promotionParente) < 0) {
        flashMessage('La promotion parente est invalide.', 'error');
    } else {
        $options[] = [
            'id_option' => $id,
            'libelle' => $libelle,
            'promotion_parente' => $promotionParente,
            'effectif' => $effectif,
        ];

        writeJson('options.json', $options);
        flashMessage('Option ajoutee avec succes.');
    }

    header('Location: ' . url('admin/options.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_option'])) {
    if (!$isSuperAdmin) {
        flashMessage('Acces refuse: reserve au super-administrateur.', 'error');
        header('Location: ' . url('admin/options.php'));
        exit;
    }

    $id = cleanText((string) ($_POST['id_option'] ?? ''));
    $libelle = cleanText((string) ($_POST['libelle'] ?? ''));
    $promotionParente = cleanText((string) ($_POST['promotion_parente'] ?? ''));
    $effectif = cleanInt($_POST['effectif'] ?? 0);

    $index = findIndexByKey($options, 'id_option', $id);

    if ($id === '' || $libelle === '' || $promotionParente === '' || $effectif <= 0) {
        flashMessage('Donnees invalides pour la modification de l\'option.', 'error');
    } elseif ($index < 0) {
        flashMessage('Option introuvable.', 'error');
    } elseif (findIndexByKey($promotions, 'id_promotion', $promotionParente) < 0) {
        flashMessage('La promotion parente est invalide.', 'error');
    } else {
        $options[$index]['libelle'] = $libelle;
        $options[$index]['promotion_parente'] = $promotionParente;
        $options[$index]['effectif'] = $effectif;
        writeJson('options.json', $options);
        flashMessage('Option modifiee avec succes.');
    }

    header('Location: ' . url('admin/options.php'));
    exit;
}

if (isset($_GET['delete'])) {
    if (!$isSuperAdmin) {
        flashMessage('Acces refuse: reserve au super-administrateur.', 'error');
        header('Location: ' . url('admin/options.php'));
        exit;
    }

    $id = cleanText((string) $_GET['delete']);
    $index = findIndexByKey($options, 'id_option', $id);

    if ($index >= 0) {
        array_splice($options, $index, 1);
        writeJson('options.json', $options);
        flashMessage('Option supprimee avec succes.');
    } else {
        flashMessage('Option introuvable.', 'error');
    }

    header('Location: ' . url('admin/options.php'));
    exit;
}

if (isset($_GET['edit'])) {
    $id = cleanText((string) $_GET['edit']);
    $index = findIndexByKey($options, 'id_option', $id);
    if ($index >= 0) {
        $editing = $options[$index];
    }
}

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card">
    <h2>Gestion des options</h2>
    <?php if (!$isSuperAdmin): ?>
        <div class="alert alert-error">Seul le super-administrateur peut ajouter, modifier ou supprimer des options.</div>
    <?php endif; ?>
    <?php if ($editing): ?>
        <?php if ($isSuperAdmin): ?>
        <form method="post">
            <input type="hidden" name="update_option" value="1">
            <div class="input-row">
                <div>
                    <label for="id_option">ID Option</label>
                    <input id="id_option" name="id_option" required readonly value="<?= htmlspecialchars((string) ($editing['id_option'] ?? '')) ?>">
                </div>
                <div>
                    <label for="libelle">Libelle</label>
                    <input id="libelle" name="libelle" required maxlength="80" value="<?= htmlspecialchars((string) ($editing['libelle'] ?? '')) ?>">
                </div>
                <div>
                    <label for="promotion_parente">Promotion parente</label>
                    <select id="promotion_parente" name="promotion_parente" required>
                        <option value="">Selectionner</option>
                        <?php foreach ($promotions as $promotion): ?>
                            <?php $value = (string) ($promotion['id_promotion'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= (($editing['promotion_parente'] ?? '') === $value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($promotion['libelle'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="effectif">Effectif</label>
                    <input id="effectif" name="effectif" type="number" min="1" required value="<?= (int) ($editing['effectif'] ?? 0) ?>">
                </div>
            </div>
            <div class="actions">
                <button class="btn btn-warning" type="submit">Enregistrer la modification</button>
                <a class="btn" href="<?= htmlspecialchars(url('admin/options.php')) ?>">Annuler</a>
            </div>
        </form>
        <?php else: ?>
            <p>Mode lecture seule pour ce compte.</p>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($isSuperAdmin): ?>
        <form method="post">
            <input type="hidden" name="add_option" value="1">
            <div class="input-row">
                <div>
                    <label for="id_option">ID Option</label>
                    <input id="id_option" name="id_option" required maxlength="20" placeholder="Ex: O5">
                </div>
                <div>
                    <label for="libelle">Libelle</label>
                    <input id="libelle" name="libelle" required maxlength="80">
                </div>
                <div>
                    <label for="promotion_parente">Promotion parente</label>
                    <select id="promotion_parente" name="promotion_parente" required>
                        <option value="">Selectionner</option>
                        <?php foreach ($promotions as $promotion): ?>
                            <option value="<?= htmlspecialchars((string) ($promotion['id_promotion'] ?? '')) ?>">
                                <?= htmlspecialchars((string) ($promotion['libelle'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="effectif">Effectif</label>
                    <input id="effectif" name="effectif" type="number" min="1" required>
                </div>
            </div>
            <button class="btn" type="submit">Ajouter l'option</button>
        </form>
        <?php else: ?>
            <p>Mode lecture seule pour ce compte.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="card table-wrap">
    <h3>Liste des options</h3>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Libelle</th>
            <th>Promotion parente</th>
            <th>Effectif</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($options as $option): ?>
            <tr>
                <td><?= htmlspecialchars((string) ($option['id_option'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($option['libelle'] ?? '')) ?></td>
                <td><?= htmlspecialchars(getGroupLabel((string) ($option['promotion_parente'] ?? ''))) ?></td>
                <td><?= htmlspecialchars((string) ($option['effectif'] ?? 0)) ?></td>
                <td>
                    <?php if ($isSuperAdmin): ?>
                        <a class="btn btn-warning" href="<?= htmlspecialchars(url('admin/options.php?edit=' . urlencode((string) ($option['id_option'] ?? '')))) ?>">Modifier</a>
                        <a class="btn btn-danger" href="<?= htmlspecialchars(url('admin/options.php?delete=' . urlencode((string) ($option['id_option'] ?? '')))) ?>" onclick="return confirm('Supprimer cette option ?')">Supprimer</a>
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
