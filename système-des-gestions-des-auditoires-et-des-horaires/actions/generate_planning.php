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

$result = generatePlanningAutomatically();

if (empty($result['errors'])) {
    flashMessage('Planning genere avec succes. Seances creees: ' . (int) $result['created']);
} else {
    $message = 'Generation partielle. Seances creees: ' . (int) $result['created'] . '. ';
    $message .= implode(' ', $result['errors']);
    flashMessage($message, 'error');
}

header('Location: ' . url('admin/planning.php'));
exit;
