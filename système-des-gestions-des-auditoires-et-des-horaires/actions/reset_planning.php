<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';
requireAdmin();
requirePermission('manage_planning');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

writeJson('planning.json', []);
flashMessage('Planning reinitialise.');

header('Location: ' . url('admin/planning.php'));
exit;
