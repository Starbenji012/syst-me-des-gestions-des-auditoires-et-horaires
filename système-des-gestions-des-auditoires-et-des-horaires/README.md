# Gestion des Auditoires et des Horaires

Application PHP sans framework pour gérer les salles, les cours, les promotions, les options, le planning et les administrateurs.

## Déploiement

1. Copier le projet sur un serveur PHP 7.1+ avec accès en écriture au dossier `data/`.
2. Placer le projet dans le répertoire web du serveur ou configurer le virtual host vers la racine du projet.
3. Vérifier que les extensions PHP de base sont disponibles, surtout `json`.
4. Ouvrir l’application dans le navigateur.
5. Se connecter avec l’administrateur par défaut si nécessaire.

## Configuration

- La configuration principale se trouve dans `config/config.php`.
- Les données sont stockées dans les fichiers JSON du dossier `data/`.
- Si le projet est déplacé dans un sous-dossier, l’URL est calculée automatiquement.

## Compte administrateur

- Identifiant par défaut : `admin`
- Le mot de passe peut être réinitialisé via les scripts du dossier `maintenance/` si nécessaire.

## Notes

- Il est recommandé de supprimer les scripts de maintenance après utilisation.
- L’application utilise un thème sombre et un système de permissions par module.
