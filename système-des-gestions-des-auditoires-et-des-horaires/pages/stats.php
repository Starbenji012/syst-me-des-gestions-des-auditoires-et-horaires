<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';

$rooms = readJson('salles.json');
$planning = sortPlanningBySlot(readJson('planning.json'));
$courses = readJson('cours.json');
$promotions = readJson('promotions.json');
$options = readJson('options.json');

$occupiedRooms = [];
$busyGroups = [];
foreach ($planning as $entry) {
    $occupiedRooms[(string) ($entry['salle'] ?? '')] = true;
    $busyGroups[(string) ($entry['groupe'] ?? '')] = true;
}

$totalRooms = count($rooms);
$totalCapacity = array_reduce($rooms, static function (int $carry, array $room): int {
    return $carry + (int) ($room['capacite'] ?? 0);
}, 0);
$roomsInUse = count(array_filter($rooms, static fn(array $room): bool => isset($occupiedRooms[(string) ($room['id'] ?? '')])));
$availableRooms = max(0, $totalRooms - $roomsInUse);
$today = date('Y-m-d');
$todayPlanning = array_values(array_filter($planning, static fn(array $entry): bool => substr((string) ($entry['creneau'] ?? ''), 0, 10) === $today));
$byDay = groupPlanningByDate($planning);

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card hero">
    <h2>Statistiques du systeme</h2>
    <p>Vue rapide des auditoires, de l'occupation actuelle et de la disponibilite des ressources.</p>
</section>

<section class="grid">
    <article class="kpi">
        <h3>Salles</h3>
        <p><?= $totalRooms ?></p>
    </article>
    <article class="kpi">
        <h3>Capacite totale</h3>
        <p><?= $totalCapacity ?></p>
    </article>
    <article class="kpi">
        <h3>Salles occupées</h3>
        <p><?= $roomsInUse ?></p>
    </article>
    <article class="kpi">
        <h3>Salles disponibles</h3>
        <p><?= $availableRooms ?></p>
    </article>
    <article class="kpi">
        <h3>Cours planifiés</h3>
        <p><?= count($planning) ?></p>
    </article>
    <article class="kpi">
        <h3>Séances aujourd'hui</h3>
        <p><?= count($todayPlanning) ?></p>
    </article>
</section>

<section class="card">
    <h3>Disponibilité des salles</h3>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Salle</th>
                <th>Capacité</th>
                <th>Etat</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rooms as $room): ?>
                <?php $roomId = (string) ($room['id'] ?? ''); ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($room['designation'] ?? '')) ?></td>
                    <td><?= (int) ($room['capacite'] ?? 0) ?></td>
                    <td><?= isset($occupiedRooms[$roomId]) ? '<span class="badge">Occupée</span>' : '<span class="badge">Disponible</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <h3>Occupation par jour</h3>
    <div class="grid">
        <?php if (empty($byDay)): ?>
            <p>Aucune séance enregistrée.</p>
        <?php else: ?>
            <?php foreach ($byDay as $date => $entries): ?>
                <article class="kpi">
                    <h3><?= htmlspecialchars(formatDayLabel($date)) ?></h3>
                    <p><?= count($entries) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
