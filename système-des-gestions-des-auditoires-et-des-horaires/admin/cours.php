<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requirePermission('manage_courses');

$cours = readJson('cours.json');
$promotions = readJson('promotions.json');
$options = readJson('options.json');

$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cours'])) {
    $id = cleanText((string) ($_POST['id_cours'] ?? ''));
    $intitule = cleanText((string) ($_POST['intitule'] ?? ''));
    $volume = cleanInt($_POST['volume_horaire'] ?? 0);
    $group = cleanText((string) ($_POST['promotion_option'] ?? ''));

    $index = findIndexByKey($cours, 'id_cours', $id);

    if ($id === '' || $intitule === '' || $volume <= 0 || $group === '') {
        flashMessage('Donnees invalides pour la modification du cours.', 'error');
    } elseif (!validateGroupId($group)) {
        flashMessage('Promotion/option invalide.', 'error');
    } elseif ($index < 0) {
        flashMessage('Cours introuvable pour modification.', 'error');
    } else {
        $cours[$index]['intitule'] = $intitule;
        $cours[$index]['volume_horaire'] = $volume;
        $cours[$index]['promotion_option'] = $group;
        writeJson('cours.json', $cours);
        flashMessage('Cours modifie avec succes.');
    }

    header('Location: ' . url('admin/cours.php'));
    exit;
}

if (isset($_GET['delete'])) {
    $id = cleanText((string) $_GET['delete']);
    $index = findIndexByKey($cours, 'id_cours', $id);

    if ($index >= 0) {
        array_splice($cours, $index, 1);
        writeJson('cours.json', $cours);
        flashMessage('Cours supprime avec succes.');
    } else {
        flashMessage('Cours introuvable.', 'error');
    }

    header('Location: ' . url('admin/cours.php'));
    exit;
}

if (isset($_GET['edit'])) {
    $id = cleanText((string) $_GET['edit']);
    $index = findIndexByKey($cours, 'id_cours', $id);
    if ($index >= 0) {
        $editing = $cours[$index];
    }
}

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card">
    <h2>Gestion des cours</h2>
    <?php if ($editing): ?>
        <form method="post">
            <input type="hidden" name="update_cours" value="1">
            <div class="input-row">
                <div>
                    <label for="id_cours">ID Cours</label>
                    <input id="id_cours" name="id_cours" required readonly value="<?= htmlspecialchars((string) ($editing['id_cours'] ?? '')) ?>">
                </div>
                <div>
                    <label for="intitule">Intitule</label>
                    <input id="intitule" name="intitule" required maxlength="120" value="<?= htmlspecialchars((string) ($editing['intitule'] ?? '')) ?>">
                </div>
                <div>
                    <label for="volume_horaire">Volume horaire (h)</label>
                    <input id="volume_horaire" name="volume_horaire" type="number" min="1" required value="<?= (int) ($editing['volume_horaire'] ?? 0) ?>">
                </div>
                <div>
                    <label for="promotion_option">Promotion/Option</label>
                    <select id="promotion_option" name="promotion_option" required>
                        <option value="">Selectionner</option>
                        <?php foreach ($promotions as $promotion): ?>
                            <?php $value = (string) ($promotion['id_promotion'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= (($editing['promotion_option'] ?? '') === $value) ? 'selected' : '' ?>>
                                Promotion: <?= htmlspecialchars((string) ($promotion['libelle'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php foreach ($options as $option): ?>
                            <?php $value = (string) ($option['id_option'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= (($editing['promotion_option'] ?? '') === $value) ? 'selected' : '' ?>>
                                Option: <?= htmlspecialchars((string) ($option['libelle'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="btn btn-warning" type="submit">Enregistrer la modification</button>
                <a class="btn" href="<?= htmlspecialchars(url('admin/cours.php')) ?>">Annuler</a>
            </div>
        </form>
    <?php else: ?>
        <form method="post" action="<?= htmlspecialchars(url('actions/add_cours.php')) ?>">
            <div class="input-row">
                <div>
                    <label for="id_cours">ID Cours</label>
                    <input id="id_cours" name="id_cours" required maxlength="20" placeholder="Ex: C10">
                </div>
                <div>
                    <label for="intitule">Intitule</label>
                    <input id="intitule" name="intitule" required maxlength="120" placeholder="Ex: Programmation Web">
                </div>
                <div>
                    <label for="volume_horaire">Volume horaire (h)</label>
                    <input id="volume_horaire" name="volume_horaire" type="number" min="1" required>
                </div>
                <div>
                    <label for="promotion_option">Promotion/Option</label>
                    <select id="promotion_option" name="promotion_option" required>
                        <option value="">Selectionner</option>
                        <?php foreach ($promotions as $promotion): ?>
                            <option value="<?= htmlspecialchars((string) ($promotion['id_promotion'] ?? '')) ?>">
                                Promotion: <?= htmlspecialchars((string) ($promotion['libelle'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php foreach ($options as $option): ?>
                            <option value="<?= htmlspecialchars((string) ($option['id_option'] ?? '')) ?>">
                                Option: <?= htmlspecialchars((string) ($option['libelle'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="btn" type="submit">Ajouter le cours</button>
        </form>
    <?php endif; ?>
</section>

<section class="card table-wrap">
    <div class="table-tools">
        <h3>Liste des cours</h3>
        <input class="table-filter" type="search" data-table-filter="#courses-table" placeholder="Rechercher un cours...">
    </div>
    <table id="courses-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Intitule</th>
            <th>Volume</th>
            <th>Groupe</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($cours as $ligne): ?>
            <tr>
                <td><?= htmlspecialchars((string) ($ligne['id_cours'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($ligne['intitule'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($ligne['volume_horaire'] ?? '')) ?> h</td>
                <td><span class="badge"><?= htmlspecialchars(getGroupLabel((string) ($ligne['promotion_option'] ?? ''))) ?></span></td>
                <td>
                    <a class="btn btn-warning" href="<?= htmlspecialchars(url('admin/cours.php?edit=' . urlencode((string) ($ligne['id_cours'] ?? '')))) ?>">Modifier</a>
                    <a class="btn btn-danger" href="<?= htmlspecialchars(url('admin/cours.php?delete=' . urlencode((string) ($ligne['id_cours'] ?? '')))) ?>" onclick="return confirm('Supprimer ce cours ?')">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
