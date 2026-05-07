<?php

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/functions.php';

$rooms = readJson('salles.json');
$planning = sortPlanningBySlot(readJson('planning.json'));
$courses = readJson('cours.json');
$promotions = readJson('promotions.json');
$options = readJson('options.json');

$today = date('Y-m-d');
$todayPlanning = array_values(array_filter($planning, static fn(array $entry): bool => substr((string) ($entry['creneau'] ?? ''), 0, 10) === $today));
$loggedIn = isAdminLoggedIn();
$isSuperAdmin = isSuperAdmin();

$occupiedRooms = [];
foreach ($planning as $entry) {
	$occupiedRooms[(string) ($entry['salle'] ?? '')] = true;
}

$totalRooms = count($rooms);
$totalCapacity = array_reduce($rooms, static function (int $carry, array $room): int {
	return $carry + (int) ($room['capacite'] ?? 0);
}, 0);
$roomsInUse = count(array_filter($rooms, static fn(array $room): bool => isset($occupiedRooms[(string) ($room['id'] ?? '')])));
$availableRooms = max(0, $totalRooms - $roomsInUse);
$byDay = groupPlanningByDate($planning);

$freeRooms = 0;
foreach ($rooms as $room) {
	$roomOccupied = false;
	foreach ($todayPlanning as $entry) {
		if (($entry['salle'] ?? '') === ($room['id'] ?? '')) {
			$roomOccupied = true;
			break;
		}
	}

	if (!$roomOccupied) {
		$freeRooms++;
	}
}

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card hero">
	<h2>Gestion intelligente des auditoires</h2>
	<p>Page unifiee: accueil, statistiques et suivi global des activites administratives.</p>
	<div class="actions">
		<a class="btn" href="#planning-global">Planning global</a>
		<?php if (!$loggedIn): ?>
			<a class="btn btn-warning" href="<?= htmlspecialchars(url('auth/login.php')) ?>">Connexion administrateur</a>
		<?php elseif ($isSuperAdmin): ?>
			<a class="btn btn-warning" href="<?= htmlspecialchars(url('admin/password.php')) ?>">Espace administration</a>
		<?php endif; ?>
	</div>
</section>

<section class="grid">
	<article class="kpi">
		<h3>Salles enregistrees</h3>
		<p><?= $totalRooms ?></p>
	</article>
	<article class="kpi">
		<h3>Capacite totale</h3>
		<p><?= $totalCapacity ?></p>
	</article>
	<article class="kpi">
		<h3>Salles occupees</h3>
		<p><?= $roomsInUse ?></p>
	</article>
	<article class="kpi">
		<h3>Salles disponibles</h3>
		<p><?= $availableRooms ?></p>
	</article>
	<article class="kpi">
		<h3>Seances aujourd'hui</h3>
		<p><?= count($todayPlanning) ?></p>
	</article>
	<article class="kpi">
		<h3>Cours planifies</h3>
		<p><?= count($planning) ?></p>
	</article>
</section>

<?php if ($loggedIn): ?>
<section class="card spacing-top">
	<h3>Vue dashboard administrateur</h3>
	<div class="grid">
		<article class="kpi">
			<h3>Nombre de salles</h3>
			<p><?= count($rooms) ?></p>
		</article>
		<article class="kpi">
			<h3>Promotions</h3>
			<p><?= count($promotions) ?></p>
		</article>
		<article class="kpi">
			<h3>Options</h3>
			<p><?= count($options) ?></p>
		</article>
		<article class="kpi">
			<h3>Cours</h3>
			<p><?= count($courses) ?></p>
		</article>
		<article class="kpi">
			<h3>Salles libres aujourd'hui</h3>
			<p><?= $freeRooms ?></p>
		</article>
	</div>
</section>
<?php endif; ?>

<section class="card spacing-top">
	<h3>Disponibilite des salles</h3>
	<div class="table-wrap">
		<table>
			<thead>
			<tr>
				<th>Salle</th>
				<th>Capacite</th>
				<th>Etat</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($rooms as $room): ?>
				<?php $roomId = (string) ($room['id'] ?? ''); ?>
				<tr>
					<td><?= htmlspecialchars((string) ($room['designation'] ?? '')) ?></td>
					<td><?= (int) ($room['capacite'] ?? 0) ?></td>
					<td><?= isset($occupiedRooms[$roomId]) ? '<span class="badge">Occupee</span>' : '<span class="badge">Disponible</span>' ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>

<section class="card spacing-top">
	<h3>Occupation par jour</h3>
	<div class="grid">
		<?php if (empty($byDay)): ?>
			<p>Aucune seance enregistree.</p>
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

<section class="card spacing-top" id="planning-global">
	<h2>Planning global</h2>
	<div class="table-wrap">
		<table>
			<thead>
			<tr>
				<th>Jour / Heure</th>
				<th>Cours</th>
				<th>Salle</th>
				<th>Groupe</th>
			</tr>
			</thead>
			<tbody>
			<?php if (empty($planning)): ?>
				<tr>
					<td colspan="4">Aucun cours planifie pour le moment.</td>
				</tr>
			<?php else: ?>
				<?php foreach ($planning as $row): ?>
					<tr>
						<td><?= htmlspecialchars(formatSlot((string) ($row['creneau'] ?? ''))) ?></td>
						<td><?= htmlspecialchars(getCourseLabel((string) ($row['cours'] ?? ''))) ?></td>
						<td><?= htmlspecialchars(getRoomLabel((string) ($row['salle'] ?? ''))) ?></td>
						<td><?= htmlspecialchars(getGroupLabel((string) ($row['groupe'] ?? ''))) ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
