<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requireSuperAdmin();
requirePermission('manage_planning');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$raw = cleanText((string) ($_POST['creneau'] ?? ''));
$slot = str_replace('T', ' ', $raw);
$roomId = cleanText((string) ($_POST['salle'] ?? ''));
$courseId = cleanText((string) ($_POST['cours'] ?? ''));
$groupId = cleanText((string) ($_POST['groupe'] ?? ''));

$result = addPlanningEntry($slot, $roomId, $courseId, $groupId);

if ($result['success']) {
    flashMessage('Seance ajoutee au planning avec succes.');
} else {
    flashMessage(implode(' ', $result['errors']), 'error');
}

header('Location: ' . url('admin/planning.php'));
exit;
