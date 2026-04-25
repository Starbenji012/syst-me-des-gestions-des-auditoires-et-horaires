<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requirePermission('manage_planning');

function planningUrl(array $params = []): string
{
    $filtered = array_filter($params, static fn($value) => $value !== '' && $value !== null);
    $query = http_build_query($filtered);

    return url('admin/planning.php' . ($query !== '' ? '?' . $query : ''));
}

function planningEntryMatches(array $entry, string $slot, string $roomId, string $courseId, string $groupId): bool
{
    return ($entry['creneau'] ?? '') === $slot
        && ($entry['salle'] ?? '') === $roomId
        && ($entry['cours'] ?? '') === $courseId
        && ($entry['groupe'] ?? '') === $groupId;
}

$planning = sortPlanningBySlot(readJson('planning.json'));
$rooms = readJson('salles.json');
$courses = readJson('cours.json');
$promotions = readJson('promotions.json');
$options = readJson('options.json');

$editingEntry = null;

if (isset($_GET['delete'])) {
    $slot = cleanText((string) ($_GET['delete_slot'] ?? ''));
    $roomId = cleanText((string) ($_GET['delete_room'] ?? ''));
    $courseId = cleanText((string) ($_GET['delete_course'] ?? ''));
    $groupId = cleanText((string) ($_GET['delete_group'] ?? ''));

    if ($slot !== '' && $roomId !== '' && $courseId !== '' && $groupId !== '' && deletePlanningEntry($slot, $roomId, $courseId, $groupId)) {
        flashMessage('Seance supprimee du planning.');
    } else {
        flashMessage('Impossible de supprimer cette seance.', 'error');
    }

    header('Location: ' . url('admin/planning.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_planning'])) {
    $originalSlot = cleanText((string) ($_POST['original_creneau'] ?? ''));
    $originalRoom = cleanText((string) ($_POST['original_salle'] ?? ''));
    $originalCourse = cleanText((string) ($_POST['original_cours'] ?? ''));
    $originalGroup = cleanText((string) ($_POST['original_groupe'] ?? ''));

    $newSlot = str_replace('T', ' ', cleanText((string) ($_POST['creneau'] ?? '')));
    $newRoom = cleanText((string) ($_POST['salle'] ?? ''));
    $newCourse = cleanText((string) ($_POST['cours'] ?? ''));
    $newGroup = cleanText((string) ($_POST['groupe'] ?? ''));

    $planning = readJson('planning.json');
    $index = -1;
    foreach ($planning as $key => $entry) {
        if (planningEntryMatches($entry, $originalSlot, $originalRoom, $originalCourse, $originalGroup)) {
            $index = $key;
            break;
        }
    }

    $validationErrors = validatePlanningInsertion($newSlot, $newRoom, $newCourse, $newGroup);
    if ($originalSlot === '' || $originalRoom === '' || $originalCourse === '' || $originalGroup === '') {
        $validationErrors[] = 'La séance originale est introuvable.';
    }

    if ($index < 0) {
        $validationErrors[] = 'La séance à modifier est introuvable.';
    }

    if ($index >= 0 && $originalSlot === $newSlot && $originalRoom === $newRoom && $originalCourse === $newCourse && $originalGroup === $newGroup) {
        $validationErrors[] = 'Aucune modification détectée.';
    }

    if (!empty($validationErrors)) {
        flashMessage(implode(' ', array_unique($validationErrors)), 'error');
    } else {
        $planning[$index] = [
            'creneau' => $newSlot,
            'salle' => $newRoom,
            'cours' => $newCourse,
            'groupe' => $newGroup,
        ];

        if (writeJson('planning.json', $planning)) {
            flashMessage('Seance modifiee avec succes.');
        } else {
            flashMessage('Impossible de modifier cette séance.', 'error');
        }
    }

    header('Location: ' . url('admin/planning.php'));
    exit;
}

if (isset($_GET['edit'])) {
    $slot = cleanText((string) ($_GET['edit_slot'] ?? ''));
    $roomId = cleanText((string) ($_GET['edit_room'] ?? ''));
    $courseId = cleanText((string) ($_GET['edit_course'] ?? ''));
    $groupId = cleanText((string) ($_GET['edit_group'] ?? ''));

    foreach ($planning as $entry) {
        if (planningEntryMatches($entry, $slot, $roomId, $courseId, $groupId)) {
            $editingEntry = $entry;
            break;
        }
    }
}

$salleFilter = cleanText((string) ($_GET['salle'] ?? ''));
$coursFilter = cleanText((string) ($_GET['cours'] ?? ''));
$groupeFilter = cleanText((string) ($_GET['groupe'] ?? ''));
$dateFilter = cleanText((string) ($_GET['date'] ?? ''));
$weekReference = cleanText((string) ($_GET['week'] ?? ($dateFilter !== '' ? $dateFilter : date('Y-m-d'))));

$filters = [
    'salle' => $salleFilter,
    'cours' => $coursFilter,
    'groupe' => $groupeFilter,
    'date' => $dateFilter,
];

$filteredPlanning = filterPlanningEntries($planning, $filters);
$weekRange = getWeekRange($weekReference);
$weekPlanning = array_values(array_filter($planning, static function (array $entry) use ($weekRange, $salleFilter, $coursFilter, $groupeFilter): bool {
    $slotDate = substr((string) ($entry['creneau'] ?? ''), 0, 10);

    if ($slotDate < $weekRange['start'] || $slotDate > $weekRange['end']) {
        return false;
    }

    if ($salleFilter !== '' && ($entry['salle'] ?? '') !== $salleFilter) {
        return false;
    }

    if ($coursFilter !== '' && ($entry['cours'] ?? '') !== $coursFilter) {
        return false;
    }

    if ($groupeFilter !== '' && ($entry['groupe'] ?? '') !== $groupeFilter) {
        return false;
    }

    return true;
}));

$selectedWeekDate = $weekRange['start'];
$prevWeek = (new DateTime($selectedWeekDate))->modify('-7 days')->format('Y-m-d');
$nextWeek = (new DateTime($selectedWeekDate))->modify('+7 days')->format('Y-m-d');
$groupedWeekPlanning = groupPlanningByDate($weekPlanning);

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card">
    <h2>Gestion du planning</h2>
    <div class="actions">
        <a class="btn btn-warning" href="<?= htmlspecialchars(url('actions/generate_planning.php')) ?>" onclick="return confirm('Generer automatiquement le planning ?')">Generer automatiquement</a>
        <a class="btn btn-danger" href="<?= htmlspecialchars(url('actions/reset_planning.php')) ?>" onclick="return confirm('Vider tout le planning ?')">Vider le planning</a>
    </div>
</section>

<section class="card">
    <h3>Filtres</h3>
    <form method="get" data-autosubmit="1">
        <div class="input-row">
            <div>
                <label for="salle">Salle</label>
                <select id="salle" name="salle">
                    <option value="">Toutes les salles</option>
                    <?php foreach ($rooms as $room): ?>
                        <?php $value = (string) ($room['id'] ?? ''); ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $salleFilter === $value ? 'selected' : '' ?>><?= htmlspecialchars((string) ($room['designation'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="cours">Cours</label>
                <select id="cours" name="cours">
                    <option value="">Tous les cours</option>
                    <?php foreach ($courses as $course): ?>
                        <?php $value = (string) ($course['id_cours'] ?? ''); ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $coursFilter === $value ? 'selected' : '' ?>><?= htmlspecialchars((string) ($course['intitule'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="groupe">Groupe</label>
                <select id="groupe" name="groupe">
                    <option value="">Tous les groupes</option>
                    <?php foreach ($promotions as $promotion): ?>
                        <?php $value = (string) ($promotion['id_promotion'] ?? ''); ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $groupeFilter === $value ? 'selected' : '' ?>><?= htmlspecialchars((string) ($promotion['libelle'] ?? '')) ?></option>
                    <?php endforeach; ?>
                    <?php foreach ($options as $option): ?>
                        <?php $value = (string) ($option['id_option'] ?? ''); ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $groupeFilter === $value ? 'selected' : '' ?>><?= htmlspecialchars((string) ($option['libelle'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="date">Date précise</label>
                <input id="date" name="date" type="date" value="<?= htmlspecialchars($dateFilter) ?>">
            </div>
            <div>
                <label for="week">Semaine</label>
                <input id="week" name="week" type="date" value="<?= htmlspecialchars($selectedWeekDate) ?>">
            </div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Appliquer les filtres</button>
            <a class="btn btn-warning" href="<?= htmlspecialchars(planningUrl(['week' => $prevWeek, 'salle' => $salleFilter, 'cours' => $coursFilter, 'groupe' => $groupeFilter, 'date' => $dateFilter])) ?>">Semaine precedente</a>
            <a class="btn btn-warning" href="<?= htmlspecialchars(planningUrl(['week' => $nextWeek, 'salle' => $salleFilter, 'cours' => $coursFilter, 'groupe' => $groupeFilter, 'date' => $dateFilter])) ?>">Semaine suivante</a>
        </div>
    </form>
</section>

<section class="card">
    <h3>Ajout manuel d'une seance</h3>
    <?php if ($editingEntry): ?>
        <form method="post">
            <input type="hidden" name="update_planning" value="1">
            <input type="hidden" name="original_creneau" value="<?= htmlspecialchars((string) ($editingEntry['creneau'] ?? '')) ?>">
            <input type="hidden" name="original_salle" value="<?= htmlspecialchars((string) ($editingEntry['salle'] ?? '')) ?>">
            <input type="hidden" name="original_cours" value="<?= htmlspecialchars((string) ($editingEntry['cours'] ?? '')) ?>">
            <input type="hidden" name="original_groupe" value="<?= htmlspecialchars((string) ($editingEntry['groupe'] ?? '')) ?>">
            <div class="input-row">
                <div>
                    <label for="creneau">Creneau</label>
                    <input id="creneau" name="creneau" type="datetime-local" required value="<?= htmlspecialchars(str_replace(' ', 'T', (string) ($editingEntry['creneau'] ?? ''))) ?>">
                </div>
                <div>
                    <label for="salle">Salle</label>
                    <select id="salle" name="salle" required>
                        <option value="">Selectionner</option>
                        <?php foreach ($rooms as $room): ?>
                            <?php $value = (string) ($room['id'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= (($editingEntry['salle'] ?? '') === $value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($room['designation'] ?? '')) ?> (<?= (int) ($room['capacite'] ?? 0) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="cours">Cours</label>
                    <select id="cours" name="cours" required>
                        <option value="">Selectionner</option>
                        <?php foreach ($courses as $course): ?>
                            <?php $value = (string) ($course['id_cours'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= (($editingEntry['cours'] ?? '') === $value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($course['intitule'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="groupe">Groupe</label>
                    <select id="groupe" name="groupe" required>
                        <option value="">Selectionner</option>
                        <?php foreach ($promotions as $promotion): ?>
                            <?php $value = (string) ($promotion['id_promotion'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= (($editingEntry['groupe'] ?? '') === $value) ? 'selected' : '' ?>>
                                Promotion: <?= htmlspecialchars((string) ($promotion['libelle'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php foreach ($options as $option): ?>
                            <?php $value = (string) ($option['id_option'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= (($editingEntry['groupe'] ?? '') === $value) ? 'selected' : '' ?>>
                                Option: <?= htmlspecialchars((string) ($option['libelle'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="btn btn-warning" type="submit">Enregistrer la modification</button>
                <a class="btn" href="<?= htmlspecialchars(url('admin/planning.php')) ?>">Annuler</a>
            </div>
        </form>
    <?php else: ?>
        <form method="post" action="<?= htmlspecialchars(url('actions/add_planning.php')) ?>">
        <div class="input-row">
            <div>
                <label for="creneau">Creneau</label>
                <input id="creneau" name="creneau" type="datetime-local" required>
            </div>
            <div>
                <label for="salle">Salle</label>
                <select id="salle" name="salle" required>
                    <option value="">Selectionner</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= htmlspecialchars((string) ($room['id'] ?? '')) ?>">
                            <?= htmlspecialchars((string) ($room['designation'] ?? '')) ?> (<?= (int) ($room['capacite'] ?? 0) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="cours">Cours</label>
                <select id="cours" name="cours" required>
                    <option value="">Selectionner</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars((string) ($course['id_cours'] ?? '')) ?>">
                            <?= htmlspecialchars((string) ($course['intitule'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="groupe">Groupe</label>
                <select id="groupe" name="groupe" required>
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
        <button class="btn" type="submit">Ajouter au planning</button>
        </form>
    <?php endif; ?>
</section>

<section class="card table-wrap">
    <h3>Resultats filtres</h3>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Jour / Heure</th>
            <th>Cours</th>
            <th>Salle</th>
            <th>Groupe</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($filteredPlanning)): ?>
            <tr><td colspan="6">Aucune seance ne correspond aux filtres choisis.</td></tr>
        <?php else: ?>
            <?php foreach ($filteredPlanning as $index => $row): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars(formatSlot((string) ($row['creneau'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars(getCourseLabel((string) ($row['cours'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars(getRoomLabel((string) ($row['salle'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars(getGroupLabel((string) ($row['groupe'] ?? ''))) ?></td>
                    <td>
                        <a class="btn btn-warning" href="<?= htmlspecialchars(planningUrl(['edit' => 1, 'edit_slot' => (string) ($row['creneau'] ?? ''), 'edit_room' => (string) ($row['salle'] ?? ''), 'edit_course' => (string) ($row['cours'] ?? ''), 'edit_group' => (string) ($row['groupe'] ?? '')])) ?>">Modifier</a>
                        <a class="btn btn-danger" href="<?= htmlspecialchars(planningUrl(['delete' => 1, 'delete_slot' => (string) ($row['creneau'] ?? ''), 'delete_room' => (string) ($row['salle'] ?? ''), 'delete_course' => (string) ($row['cours'] ?? ''), 'delete_group' => (string) ($row['groupe'] ?? '')])) ?>" onclick="return confirm('Supprimer cette seance ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <div class="actions" style="justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">Vue hebdomadaire</h3>
        <div class="badge">Du <?= htmlspecialchars(formatDayLabel($weekRange['start'])) ?> au <?= htmlspecialchars(formatDayLabel($weekRange['end'])) ?></div>
    </div>

    <?php if (empty($groupedWeekPlanning)): ?>
        <p>Aucune séance sur cette semaine.</p>
    <?php else: ?>
        <?php foreach ($weekRange['dates'] as $date): ?>
            <div class="card" style="margin-top: 14px;">
                <h4><?= htmlspecialchars(formatDayLabel($date)) ?></h4>
                <?php $dayEntries = $groupedWeekPlanning[$date] ?? []; ?>
                <?php if (empty($dayEntries)): ?>
                    <p>Aucune séance ce jour-là.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>Heure</th>
                                <th>Cours</th>
                                <th>Salle</th>
                                <th>Groupe</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($dayEntries as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars(substr((string) ($row['creneau'] ?? ''), 11)) ?></td>
                                    <td><?= htmlspecialchars(getCourseLabel((string) ($row['cours'] ?? ''))) ?></td>
                                    <td><?= htmlspecialchars(getRoomLabel((string) ($row['salle'] ?? ''))) ?></td>
                                    <td><?= htmlspecialchars(getGroupLabel((string) ($row['groupe'] ?? ''))) ?></td>
                                    <td>
                                        <a class="btn btn-danger" href="<?= htmlspecialchars(planningUrl(['delete' => 1, 'delete_slot' => (string) ($row['creneau'] ?? ''), 'delete_room' => (string) ($row['salle'] ?? ''), 'delete_course' => (string) ($row['cours'] ?? ''), 'delete_group' => (string) ($row['groupe'] ?? '')])) ?>" onclick="return confirm('Supprimer cette seance ?')">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
