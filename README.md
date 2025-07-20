# SmartAccess UCB

Système de gestion d'accès pour l'Université Catholique de Bukavu

## Description

SmartAccess UCB est un système complet de gestion d'accès aux salles pour les étudiants de l'Université Catholique de Bukavu. Il permet aux administrateurs de gérer les étudiants, les salles et les autorisations d'accès de manière efficace.

## Fonctionnalités

### 🔐 Authentification Admin
- Système de login/logout sécurisé
- Protection des pages par session PHP
- Gestion des sessions avec timeout

### 👨‍🎓 Gestion des Étudiants
- Interface Vue.js dynamique pour la gestion des étudiants
- Import automatique depuis l'API UCB par matricule
- Recherche et filtrage en temps réel
- Validation des matricules (format XX/YY/ZZZ)
 Validation des matricules (format XX/YY.ZZZZZ)

### 🏢 Gestion des Salles
- Interface Vue.js pour la gestion des salles
- Informations détaillées (nom, localisation, capacité, description)
- Recherche et filtrage

### 🔑 Attribution des Accès
- **Attribution individuelle** : associer un étudiant à une salle
- **Attribution groupée** : attribution par faculté/promotion via API UCB
- Gestion des niveaux d'accès (Lecture, Écriture, Admin)
- Périodes de validité configurables

### 📊 Tableau de Bord
- Statistiques en temps réel
- Historique des accès récents
- Actions rapides
- Interface responsive

### 🔍 API de Vérification
- Endpoint REST pour vérifier les accès
- Format JSON standardisé
- Historique automatique des tentatives d'accès

## Technologies Utilisées

- **Backend** : PHP natif (sans framework)
- **Base de données** : MySQL
- **Frontend** : Bootstrap 5 + Vue.js 3
- **API** : REST avec réponses JSON
- **Intégration** : API UCB pour import des données

## Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extensions PHP : mysqli, json

### Étapes d'installation

1. **Cloner le projet**
   ```bash
   git clone [url-du-repo]
   cd smartaccess-ucb
   ```

2. **Configurer la base de données**
   ```bash
   # Importer le script SQL
   mysql -u root -p < database/smartaccess_ucb.sql
   ```

3. **Configurer la connexion**
   Modifier `includes/db.php` avec vos paramètres :
   ```php
   $config = [
       'host' => 'localhost',
       'username' => 'votre_utilisateur',
       'password' => 'votre_mot_de_passe',
       'database' => 'smartaccess_ucb'
   ];
   ```

4. **Configurer le serveur web**
   - Pointer le DocumentRoot vers le dossier du projet
   - Activer mod_rewrite si nécessaire

## Structure du Projet

```
smartaccess-ucb/
├── admin/                  # Pages d'administration
│   ├── etudiants.php      # Gestion des étudiants
│   ├── salles.php         # Gestion des salles
│   └── acces.php          # Gestion des accès
├── api/                   # APIs REST
│   ├── verifier_acces.php # API de vérification d'accès
│   ├── students.php       # API étudiants
│   ├── salles.php         # API salles
│   └── autorisations.php  # API autorisations
├── includes/              # Fichiers utilitaires
│   ├── db.php            # Connexion base de données
│   ├── functions.php     # Fonctions métier
│   └── session.php       # Gestion des sessions
├── database/             # Scripts SQL
│   └── smartaccess_ucb.sql
├── assets/               # Ressources statiques
│   ├── css/
│   └── js/
├── index.php            # Page d'accueil
├── login.php            # Connexion admin
├── logout.php           # Déconnexion
└── dashboard.php        # Tableau de bord
```

## API de Vérification d'Accès

### Endpoint
```
GET /api/verifier_acces.php?matricule=XXX&salle_id=YYY
```

### Réponse - Accès Autorisé
```json
{
    "status": "ACCES AUTORISE",
    "etudiant": "MUKAMBA Jean",
    "salle": "Salle Informatique A",
    "matricule": "05/23.09319",
    "salle_id": 1,
    "timestamp": "2024-01-15 10:30:00"
}
```

### Réponse - Accès Refusé
```json
{
    "status": "ACCES REFUSE",
    "message": "Aucune autorisation valide trouvée",
    "matricule": "05/23.99999",
    "salle_id": 1,
    "timestamp": "2024-01-15 10:30:00"
}
```

## Intégration API UCB

### Import Étudiant
```
GET https://akhademie.ucbukavu.ac.cd/api/v1/school-students/read-by-matricule?matricule=05/23.09319
```

### Liste Facultés/Promotions
```
GET https://akhademie.ucbukavu.ac.cd/api/v1/school/entity-main-list?entity_id=undefined&promotion_id=1&traditional=undefined
```

## Comptes par Défaut

### Administrateur
- **Utilisateur** : `admin`
- **Mot de passe** : `admin123`

## Base de Données

### Tables Principales
- `admins` : Comptes administrateurs
- `etudiants` : Informations des étudiants
- `salles` : Informations des salles
- `autorisations` : Autorisations d'accès
- `historiques_acces` : Historique des tentatives d'accès

## Sécurité

- Sessions PHP sécurisées avec timeout
- Validation des entrées utilisateur
- Requêtes préparées pour éviter les injections SQL
- Tokens CSRF pour les formulaires
- Soft delete pour préserver l'historique

## Déploiement

### Serveur Local (XAMPP)
1. Copier le projet dans `htdocs/smartaccess-ucb`
2. Démarrer Apache et MySQL
3. Importer la base de données
4. Accéder à `http://localhost/smartaccess-ucb`

### Hébergeur Gratuit (000webhost)
1. Uploader les fichiers via FTP
2. Créer la base de données MySQL
3. Modifier la configuration dans `includes/db.php`
4. Importer le script SQL

## Support

Pour toute question ou problème :
- Consulter la documentation
- Vérifier les logs d'erreur PHP
- Contacter l'équipe de développement

## Licence

Ce projet est développé pour l'Université Catholique de Bukavu.

---

**SmartAccess UCB** - Système de Gestion d'Accès
Université Catholique de Bukavu - 2024