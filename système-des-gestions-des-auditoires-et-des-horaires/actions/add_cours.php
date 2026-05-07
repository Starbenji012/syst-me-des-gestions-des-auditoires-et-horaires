<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requireSuperAdmin();
requirePermission('manage_courses');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$id = cleanText((string) ($_POST['id_cours'] ?? ''));
$intitule = cleanText((string) ($_POST['intitule'] ?? ''));
$volume = cleanInt($_POST['volume_horaire'] ?? 0);
$promotionOption = cleanText((string) ($_POST['promotion_option'] ?? ''));

$cours = readJson('cours.json');

if ($id === '' || $intitule === '' || $volume <= 0 || $promotionOption === '') {
    flashMessage('Donnees invalides pour le cours.', 'error');
} elseif (findIndexByKey($cours, 'id_cours', $id) >= 0) {
    flashMessage('ID cours deja utilise.', 'error');
} elseif (!validateGroupId($promotionOption)) {
    flashMessage('Promotion/option invalide.', 'error');
} else {
    $cours[] = [
        'id_cours' => $id,
        'intitule' => $intitule,
        'volume_horaire' => $volume,
        'promotion_option' => $promotionOption,
    ];

    if (writeJson('cours.json', $cours)) {
        flashMessage('Cours ajoute avec succes.');
    } else {
        flashMessage('Erreur lors de l\'enregistrement du cours.', 'error');
    }
}

header('Location: ' . url('admin/cours.php'));
exit;
