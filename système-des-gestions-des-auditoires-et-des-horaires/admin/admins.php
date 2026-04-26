<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$query = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== ''
    ? ('?' . (string) $_SERVER['QUERY_STRING'])
    : '';

header('Location: ' . url('admin/password.php' . $query));
exit;
