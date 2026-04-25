<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';

adminLogout();
flashMessage('Deconnexion effectuee.');

header('Location: ' . url('auth/login.php'));
exit;
