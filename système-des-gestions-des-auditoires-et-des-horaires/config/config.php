<?php

declare(strict_types=1);

define('APP_NAME', 'Gestion des Auditoires');
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$12$7OLHIVG3N/HXcnrsCZWyc.AKj7MSuMxBnKLD4QJ5KdghA3hO7W7x2');

date_default_timezone_set('Africa/Kinshasa');

$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : '';
$basePathReal = realpath(BASE_PATH);

$computedBaseUrl = '';
if ($documentRoot && $basePathReal && strpos($basePathReal, $documentRoot) === 0) {
    $computedBaseUrl = str_replace('\\', '/', substr($basePathReal, strlen($documentRoot)));
}

define('BASE_URL', rtrim($computedBaseUrl, '/'));

function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    if ($path === '') {
        return BASE_URL === '' ? '/' : BASE_URL . '/';
    }

    return (BASE_URL === '' ? '' : BASE_URL) . '/' . $path;
}
