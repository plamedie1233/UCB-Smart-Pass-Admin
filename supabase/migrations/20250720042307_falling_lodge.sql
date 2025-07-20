-- Base de données SmartAccess UCB
-- Système de gestion d'accès pour l'Université Catholique de Bukavu

CREATE DATABASE IF NOT EXISTS smartaccess_ucb;
USE smartaccess_ucb;

-- Table des administrateurs
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    nom VARCHAR(100),
    prenom VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des étudiants
CREATE TABLE etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    faculte VARCHAR(100),
    promotion VARCHAR(50),
    uid_firebase VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE
);

-- Table des salles
CREATE TABLE salles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_salle VARCHAR(100) NOT NULL,
    localisation VARCHAR(200),
    description TEXT,
    capacite INT,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des autorisations d'accès
CREATE TABLE autorisations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    salle_id INT NOT NULL,
    niveau_acces ENUM('LECTURE', 'ECRITURE', 'ADMIN') DEFAULT 'LECTURE',
    actif BOOLEAN DEFAULT TRUE,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (salle_id) REFERENCES salles(id) ON DELETE CASCADE,
    INDEX idx_etudiant_salle (etudiant_id, salle_id),
    INDEX idx_dates (date_debut, date_fin)
);

-- Table historique des accès
CREATE TABLE historiques_acces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT,
    salle_id INT,
    matricule_utilise VARCHAR(20),
    type_acces ENUM('ENTREE', 'SORTIE') NOT NULL,
    statut ENUM('AUTORISE', 'REFUSE') NOT NULL,
    date_entree DATETIME NOT NULL,
    date_sortie DATETIME NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE SET NULL,
    FOREIGN KEY (salle_id) REFERENCES salles(id) ON DELETE SET NULL,
    INDEX idx_matricule (matricule_utilise),
    INDEX idx_date (date_entree)
);

-- Données initiales

-- Admin par défaut
INSERT INTO admins (username, password, email, nom, prenom) VALUES 
('admin', 'admin123', 'admin@ucbukavu.ac.cd', 'Administrateur', 'Système');

-- Étudiants de test
INSERT INTO etudiants (matricule, nom, prenom, email, faculte, promotion) VALUES 
('05/23/001', 'MUKAMBA', 'Jean', 'jean.mukamba@ucbukavu.ac.cd', 'Sciences Informatiques', '2023-2024'),
('05/23/002', 'NABINTU', 'Marie', 'marie.nabintu@ucbukavu.ac.cd', 'Sciences Informatiques', '2023-2024'),
('02/22/015', 'BAHATI', 'Pierre', 'pierre.bahati@ucbukavu.ac.cd', 'Sciences Économiques', '2022-2023'),
('01/24/008', 'FURAHA', 'Grace', 'grace.furaha@ucbukavu.ac.cd', 'Médecine', '2024-2025');

-- Salles de test
INSERT INTO salles (nom_salle, localisation, description, capacite) VALUES 
('Salle Informatique A', 'Bâtiment Sciences - 1er étage', 'Salle équipée de 30 ordinateurs', 30),
('Amphithéâtre Central', 'Bâtiment Principal - RDC', 'Grand amphithéâtre pour conférences', 200),
('Laboratoire Chimie', 'Bâtiment Sciences - 2ème étage', 'Laboratoire de chimie générale', 25),
('Bibliothèque Principale', 'Bâtiment Central - 1er étage', 'Espace de lecture et recherche', 100),
('Salle de Réunion', 'Administration - 2ème étage', 'Salle pour réunions administratives', 15);

-- Autorisations de test
INSERT INTO autorisations (etudiant_id, salle_id, niveau_acces, date_debut, date_fin) VALUES 
(1, 1, 'LECTURE', '2024-01-01 08:00:00', '2024-12-31 18:00:00'),
(1, 4, 'LECTURE', '2024-01-01 08:00:00', '2024-12-31 20:00:00'),
(2, 1, 'LECTURE', '2024-01-01 08:00:00', '2024-12-31 18:00:00'),
(3, 2, 'LECTURE', '2024-01-01 08:00:00', '2024-12-31 18:00:00'),
(4, 3, 'ECRITURE', '2024-01-01 08:00:00', '2024-12-31 18:00:00');

-- Historique de test
INSERT INTO historiques_acces (etudiant_id, salle_id, matricule_utilise, type_acces, statut, date_entree) VALUES 
(1, 1, '05/23/001', 'ENTREE', 'AUTORISE', '2024-01-15 09:30:00'),
(2, 1, '05/23/002', 'ENTREE', 'AUTORISE', '2024-01-15 10:15:00'),
(3, 2, '02/22/015', 'ENTREE', 'REFUSE', '2024-01-15 14:20:00');