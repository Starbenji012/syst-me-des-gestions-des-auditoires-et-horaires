<?php
declare(strict_types=1);

/**
 * Script de réinitialisation forcée du mot de passe admin
 * Génère un nouveau hash bcrypt sécurisé
 */

$adminJsonFile = __DIR__ . '/../data/admin.json';
$newPassword = 'admin123';

$passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);

$content = file_get_contents($adminJsonFile);
$data = json_decode($content, true);

if (!is_array($data) || !isset($data['admins'])) {
    die('Erreur: Format admin.json invalide');
}

$updated = false;
foreach ($data['admins'] as &$admin) {
    if (($admin['username'] ?? '') === 'admin') {
        $admin['password_hash'] = $passwordHash;
        $admin['is_active'] = true;
        $updated = true;
        break;
    }
}

if (!$updated) {
    die('Erreur: Compte admin introuvable');
}

file_put_contents($adminJsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation réussie</title>
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

        .box {
            background: white;
            max-width: 500px;
            width: 100%;
            padding: 50px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(27, 67, 50, 0.3);
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        h1 {
            color: #1b4332;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .success {
            color: #2d6a4f;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .warning-box {
            background: #d1e7dd;
            border-left: 4px solid #2d6a4f;
            padding: 15px 20px;
            border-radius: 4px;
            margin: 25px 0;
            color: #0f5132;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin: 30px 0;
            justify-content: center;
        }

        .btn {
            background: #2d6a4f;
            color: white;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn:hover {
            background: #1b4332;
            box-shadow: 0 4px 12px rgba(27, 67, 50, 0.3);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #40916c;
        }

        .btn-secondary:hover {
            background: #2d6a4f;
        }

        .spacer {
            flex: 1;
            min-height: 20px;
        }

        .credentials-section {
            border-top: 2px solid #e9ecef;
            padding-top: 30px;
            margin-top: auto;
        }

        .credentials-title {
            color: #2d6a4f;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .credentials {
            background: #f1f3f2;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #2d6a4f;
            font-family: 'Courier New', monospace;
            text-align: left;
        }

        .credential-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #d0d5d3;
        }

        .credential-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .credential-label {
            font-size: 12px;
            color: #2d6a4f;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .credential-value {
            font-size: 16px;
            color: #1b4332;
            font-weight: 600;
            word-break: break-all;
        }

        .info-text {
            color: #6c757d;
            font-size: 13px;
            margin-top: 20px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>✅ Réinitialisation réussie</h1>
        <p class="success">Votre mot de passe admin a été réinitialisé avec succès!</p>

        <div class="warning-box">
            <strong>⚠️ Sécurité importante:</strong> Vous devez changer ce mot de passe après votre première connexion.
        </div>

        <div class="action-buttons">
            <a class="btn" href="../auth/login.php">Se connecter</a>
        </div>

        <div class="spacer"></div>

        <div class="credentials-section">
            <div class="credentials-title">Identifiants de connexion</div>
            <div class="credentials">
                <div class="credential-item">
                    <div class="credential-label">Identifiant</div>
                    <div class="credential-value">admin</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Mot de passe</div>
                    <div class="credential-value">admin123</div>
                </div>
            </div>
            <p class="info-text">Ne partagez ces identifiants avec personne et changez le mot de passe dès que possible.</p>
        </div>
    </div>
</body>
</html>
