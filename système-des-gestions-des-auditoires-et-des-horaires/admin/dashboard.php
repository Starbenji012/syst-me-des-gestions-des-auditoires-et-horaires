<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();

$salles = readJson('salles.json');
$promotions = readJson('promotions.json');
$options = readJson('options.json');
$cours = readJson('cours.json');
$planning = readJson('planning.json');

require_once INCLUDES_PATH . '/header.php';
?>

<section class="card">
    <h2>Tableau de bord administrateur</h2>
    <p>Pilotez les ressources pedagogiques et generez un planning coherent.</p>
</section>

<section class="grid">
    <article class="kpi">
        <h3>Nombre de salles</h3>
        <p><?= count($salles) ?></p>
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
        <p><?= count($cours) ?></p>
    </article>
    <article class="kpi">
        <h3>Seances planifiees</h3>
        <p><?= count($planning) ?></p>
    </article>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
