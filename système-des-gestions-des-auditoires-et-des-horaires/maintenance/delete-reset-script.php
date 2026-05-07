<?php
declare(strict_types=1);

/**
 * Script pour supprimer le script de réinitialisation du mot de passe
 * À utiliser après avoir changé le mot de passe initial
 */

$resetScript = __DIR__ . '/reset-admin-password.php';
$deleteScript = __FILE__;

if (file_exists($resetScript)) {
    unlink($resetScript);
}

if (file_exists($deleteScript)) {
    unlink($deleteScript);
}

header('Location: ../index.php');
exit;
