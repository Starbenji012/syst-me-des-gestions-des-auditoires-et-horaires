<?php

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/functions.php';

$planning = readJson('planning.json');

$rooms = readJson('salles.json');
$planning = sortPlanningBySlot($planning);

$today = date('Y-m-d');
$todayPlanning = array_values(array_filter($planning, static fn(array $entry): bool => substr((string) ($entry['creneau'] ?? ''), 0, 10) === $today));
$loggedIn = isAdminLoggedIn();
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
	<p>Planifiez les cours, évitez les conflits et optimisez l'utilisation des salles en temps réel.</p>
	<div class="actions">
		<a class="btn" href="<?= htmlspecialchars(url('pages/stats.php')) ?>">Voir les statistiques</a>
		<?php if (!$loggedIn): ?>
			<a class="btn btn-warning" href="<?= htmlspecialchars(url('auth/login.php')) ?>">Accès administrateur</a>
		<?php else: ?>
			<a class="btn btn-warning" href="<?= htmlspecialchars(url('admin/dashboard.php')) ?>">Tableau admin</a>
		<?php endif; ?>
	</div>
</section>

<section class="grid">
	<article class="kpi">
		<h3>Salles enregistrées</h3>
		<p><?= count($rooms) ?></p>
	</article>
	<article class="kpi">
		<h3>Séances aujourd'hui</h3>
		<p><?= count($todayPlanning) ?></p>
	</article>
	<article class="kpi">
		<h3>Salles libres aujourd'hui</h3>
		<p><?= $freeRooms ?></p>
	</article>
</section>

<section class="card">
	<h3>Planning du jour</h3>
	<div class="table-wrap">
		<table>
			<thead>
			<tr>
				<th>Heure</th>
				<th>Cours</th>
				<th>Salle</th>
				<th>Groupe</th>
			</tr>
			</thead>
			<tbody>
			<?php if (empty($todayPlanning)): ?>
				<tr>
					<td colspan="4">Aucune séance planifiée aujourd'hui.</td>
				</tr>
			<?php else: ?>
				<?php foreach ($todayPlanning as $row): ?>
					<tr>
						<td><?= htmlspecialchars(substr((string) ($row['creneau'] ?? ''), 11)) ?></td>
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

<section class="card">
	<h2>Bienvenue</h2>
	<p>
		Cette application permet de planifier les cours, reserver les salles et gerer les conflits automatiquement.
		L'espace administrateur permet d'ajouter les donnees et de generer le planning sans conflit.
	</p>
	<p>
		<?php if (!$loggedIn): ?>
			<a class="btn" href="<?= htmlspecialchars(url('auth/login.php')) ?>">Acceder a l'administration</a>
		<?php else: ?>
			<a class="btn" href="<?= htmlspecialchars(url('admin/dashboard.php')) ?>">Ouvrir l'espace administrateur</a>
		<?php endif; ?>
		<a class="btn btn-warning" href="<?= htmlspecialchars(url('pages/stats.php')) ?>">Statistiques</a>
	</p>
</section>

<section class="card">
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
