<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requireSuperAdmin();
requirePermission('manage_rooms');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$id = cleanText((string) ($_POST['id'] ?? ''));
$designation = cleanText((string) ($_POST['designation'] ?? ''));
$capacite = cleanInt($_POST['capacite'] ?? 0);

$salles = readJson('salles.json');

if ($id === '' || $designation === '' || $capacite <= 0) {
    flashMessage('Donnees invalides pour la salle.', 'error');
} elseif (findIndexByKey($salles, 'id', $id) >= 0) {
    flashMessage('ID salle deja utilise.', 'error');
} else {
    $salles[] = [
        'id' => $id,
        'designation' => $designation,
        'capacite' => $capacite,
    ];

    if (writeJson('salles.json', $salles)) {
        flashMessage('Salle ajoutee avec succes.');
    } else {
        flashMessage('Erreur lors de l\'enregistrement.', 'error');
    }
}

header('Location: ' . url('admin/salles.php'));
exit;
