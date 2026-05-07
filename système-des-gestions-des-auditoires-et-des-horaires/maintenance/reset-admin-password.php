<?php
declare(strict_types=1);

/**
 * Script de réinitialisation du mot de passe admin
 * À UTILISER UNIQUEMENT EN CAS D'URGENCE
 * À SUPPRIMER APRÈS UTILISATION
 */

$newPassword = 'admin123';
$adminUsername = 'admin';
$adminJsonFile = __DIR__ . '/../data/admin.json';

if (!file_exists($adminJsonFile)) {
    die('❌ Erreur: Fichier admin.json introuvable.');
}

$adminData = json_decode(file_get_contents($adminJsonFile), true);
if (!$adminData || !isset($adminData['admins'])) {
    die('❌ Erreur: Format du fichier admin.json invalide.');
}

$adminFound = false;
foreach ($adminData['admins'] as &$admin) {
    if (($admin['username'] ?? '') === $adminUsername) {
        $admin['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $adminFound = true;
        break;
    }
}

if (!$adminFound) {
    die("❌ Erreur: Admin '$adminUsername' introuvable.");
}

$success = file_put_contents(
    $adminJsonFile,
    json_encode($adminData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

if ($success === false) {
    die('❌ Erreur: Impossible de sauvegarder le fichier admin.json.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 50px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(27, 67, 50, 0.3);
            max-width: 500px;
            width: 100%;
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        h1 {
            color: #1b4332;
            margin-bottom: 15px;
            font-size: 28px;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #2d6a4f;
            font-weight: 600;
        }

        .password-box {
            background: #f1f3f2;
            border: 2px solid #2d6a4f;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #1b4332;
            word-break: break-all;
            letter-spacing: 1px;
        }

        .warning {
            background: #fff3cd;
            color: #664d03;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #ff9800;
            text-align: left;
            font-size: 14px;
        }

        .warning strong {
            display: block;
            margin-bottom: 10px;
            color: #664d03;
            font-size: 15px;
        }

        .warning ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .warning li {
            margin: 5px 0;
            line-height: 1.5;
        }

        .button-group {
            margin: 30px 0;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #2d6a4f;
            color: white;
        }

        .btn-primary:hover {
            background: #1b4332;
            box-shadow: 0 4px 12px rgba(27, 67, 50, 0.3);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
            transform: translateY(-2px);
        }

        .info {
            color: #6c757d;
            font-size: 13px;
            margin-top: 25px;
            line-height: 1.6;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
        }

        .spacer {
            flex: 1;
            min-height: 20px;
        }

        p {
            color: #495057;
            margin: 10px 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Mot de passe réinitialisé</h1>

        <div class="success">
            <strong>Succès!</strong> Le mot de passe du super-administrateur a été réinitialisé.
        </div>

        <p>Utilisez ces identifiants pour vous connecter :</p>

        <div style="background: #f1f3f2; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #2d6a4f;">
            <div style="margin-bottom: 15px;">
                <strong style="color: #2d6a4f;">Username:</strong>
                <div style="font-family: monospace; font-size: 16px; color: #1b4332; margin-top: 5px;">admin</div>
            </div>
            <div>
                <strong style="color: #2d6a4f;">Nouveau mot de passe:</strong>
                <div class="password-box"><?php echo htmlspecialchars($newPassword); ?></div>
            </div>
        </div>

        <div class="warning">
            <strong>⚠️ Important:</strong>
            <ul>
                <li>Changez ce mot de passe après connexion via "Administration"</li>
                <li>Ce script de réinitialisation doit être supprimé pour des raisons de sécurité</li>
                <li>Ne partagez pas le mot de passe avec d'autres personnes</li>
            </ul>
        </div>

        <div class="spacer"></div>

        <div class="button-group">
            <a href="../auth/login.php" class="btn btn-primary">Se connecter</a>
            <a href="javascript:void(0)" class="btn btn-danger" onclick="if(confirm('Êtes-vous sûr? Ce script ne doit pas rester sur le serveur.')) window.location='delete-reset-script.php'">Supprimer ce script</a>
        </div>

        <div class="info">
            <p>Une fois connecté, allez à <strong>"Administration" > "Changer mon mot de passe"</strong> pour définir un nouveau mot de passe sécurisé.</p>
        </div>
    </div>
</body>
</html>
