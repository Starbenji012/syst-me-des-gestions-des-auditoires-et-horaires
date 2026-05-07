<?php
/**
 * Script de diagnostic du système d'authentification
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . '/functions.php';

$adminData = readJson('admin.json');

echo "<h2>Diagnostic d'authentification</h2>";

echo "<h3>Données brutes du fichier admin.json:</h3>";
echo "<pre>" . htmlspecialchars(json_encode($adminData, JSON_PRETTY_PRINT)) . "</pre>";

echo "<h3>Admins après readAdminStore():</h3>";
$store = readAdminStore();
echo "<pre>" . htmlspecialchars(json_encode($store, JSON_PRETTY_PRINT)) . "</pre>";

echo "<h3>Test de vérification des identifiants:</h3>";

// Test avec admin123
$testPassword = 'admin123';
$admin = getAdminByUsername('admin');

if ($admin === null) {
    echo "<p><strong style='color:red;'>❌ Admin 'admin' introuvable!</strong></p>";
} else {
    echo "<p><strong>Admin trouvé:</strong></p>";
    echo "<pre>" . htmlspecialchars(json_encode($admin, JSON_PRETTY_PRINT)) . "</pre>";
    
    echo "<p><strong>Test password_verify avec 'admin123':</strong></p>";
    $hash = (string) ($admin['password_hash'] ?? '');
    echo "Hash: " . htmlspecialchars(substr($hash, 0, 20) . '...') . "<br>";
    echo "is_active: " . var_export($admin['is_active'] ?? false, true) . "<br>";
    $verify = password_verify($testPassword, $hash);
    echo "password_verify result: " . var_export($verify, true) . "<br>";
    
    if ($verify && !empty($admin['is_active'])) {
        echo "<p style='color:green;'><strong>✅ La connexion devrait fonctionner!</strong></p>";
    } else {
        echo "<p style='color:red;'><strong>❌ Problème détecté:</strong></p>";
        if (!$verify) echo "- Le mot de passe ne correspond pas<br>";
        if (empty($admin['is_active'])) echo "- Le compte n'est pas actif<br>";
    }
}

?>
