# Gestion des Auditoires et des Horaires

Application PHP sans framework pour gérer les salles, les cours, les promotions, les options, le planning et les administrateurs de manière efficace et sécurisée.

## Auteurs

**KINTAUDI NDINDA BENJAMINE**  
**INAKA LA JOIE La JOIE**

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

## Fonctionnalités principales

- **Gestion des salles** : Créer, modifier et supprimer les auditoires avec capacité
- **Gestion des cours** : Planifier les cours par promotion ou option
- **Gestion des promotions et options** : Structures pédagogiques complètes (L1-L4, Master, Doctorat)
- **Planification automatique** : Générer le planning en respectant les contraintes
- **Système de droits** : Permissions granulaires par module (salles, cours, planning, etc.)
- **Interface sombre** : Thème dark moderne avec palette verte harmonisée
- **Recherche et filtrage** : Outils de filtrage dans les tableaux administratifs

## Notes

- Il est recommandé de supprimer les scripts de maintenance après utilisation.
- L'application utilise un thème sombre et un système de permissions par module.
- Consulter le rapport technique (`docs/rapport_gestion_auditoires.tex`) pour plus de détails.
