-- ============================================
-- FoodSaver – Script SQL Partie 4
-- Base de données: foodsaver_db
-- Importer dans phpMyAdmin avant de tester
-- ============================================

CREATE DATABASE IF NOT EXISTS foodsaver_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foodsaver_db;

-- ============================================
-- TABLE 1 : aliments
-- ============================================
CREATE TABLE IF NOT EXISTS aliments (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    nom                      VARCHAR(100) NOT NULL,
    type                     ENUM('Légume','Fruit','Produit laitier','Céréale','Plat préparé','Produit frais') NOT NULL,
    methode_conservation     TEXT NOT NULL,
    duree_conservation_jours INT NOT NULL DEFAULT 1,
    photo                    VARCHAR(255) DEFAULT 'default_aliment.jpg',
    date_ajout               TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 2 : recettes
CREATE TABLE IF NOT EXISTS recettes (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nom               VARCHAR(150) NOT NULL,
    categorie         VARCHAR(80)  NOT NULL,
    temps_preparation INT          NOT NULL COMMENT 'en minutes',
    difficulte        ENUM('Facile','Moyen','Difficile') NOT NULL DEFAULT 'Facile',
    description       TEXT NOT NULL,
    photo             VARCHAR(255) DEFAULT 'default_recette.jpg',
    date_creation     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 3 : contacts
CREATE TABLE IF NOT EXISTS contacts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    sujet      VARCHAR(200) NOT NULL,
    message    TEXT         NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut     ENUM('nouveau','lu','repondu') NOT NULL DEFAULT 'nouveau'
);

-- ============================================
-- DONNÉES : aliments (10 enregistrements)
-- ============================================
INSERT INTO aliments (nom, type, methode_conservation, duree_conservation_jours, photo) VALUES
('Poulet cuit',   'Plat préparé',   'Conserver au réfrigérateur dans une boîte hermétique.',         3,  'poulet.jpg'),
('Riz cuit',      'Céréale',        'Mettre au frais rapidement après cuisson, consommer sous 48h.', 2,  'riz.jpg'),
('Pâtes cuites',  'Plat préparé',   'Conserver dans un récipient fermé au réfrigérateur.',           3,  'pate.jpg'),
('Tomates',       'Légume',         'Conserver à température ambiante loin du soleil direct.',       7,  'salade.jpg'),
('Carottes',      'Légume',         'Mettre au réfrigérateur dans un sac perforé.',                 14,  'salade.jpg'),
('Pommes',        'Fruit',          'Conserver au frais, séparées des autres fruits.',              21,  'smoothie.jpg'),
('Bananes',       'Fruit',          'Garder à température ambiante, loin de l\'humidité.',           5,  'smoothie.jpg'),
('Lait',          'Produit laitier','Garder au frais entre 2–4°C et bien refermer après usage.',     5,  'default_aliment.jpg'),
('Fromage',       'Produit laitier','Envelopper dans du papier alimentaire, réfrigérer.',           14,  'default_aliment.jpg'),
('Poisson frais', 'Produit frais',  'Conserver au réfrigérateur et consommer sous 24 heures.',       1,  'poisson.jpg');

-- ============================================
-- DONNÉES : recettes (8 enregistrements)
-- ============================================
INSERT INTO recettes (nom, categorie, temps_preparation, difficulte, description, photo) VALUES
('Burger au bœuf maison',     'Plat principal', 30, 'Moyen',    'Burger juteux avec steak haché, cheddar fondu et légumes frais.',   'burger.jpg'),
('Salade Méditerranéenne',    'Entrée',         15, 'Facile',   'Salade colorée aux olives, feta, tomates et concombre.',            'salade.jpg'),
('Pizza Margherita',          'Plat principal', 45, 'Moyen',    'Pizza classique avec sauce tomate, mozzarella et basilic frais.',   'pizza.jpg'),
('Gratin dauphinois',         'Plat principal', 90, 'Moyen',    'Gratin de pommes de terre à la crème et au fromage gratiné.',      'gratin.jpg'),
('Soupe de légumes d\'hiver', 'Soupe',          40, 'Facile',   'Soupe réconfortante avec carottes, poireaux et pommes de terre.',   'soupe.jpg'),
('Omelette aux herbes',       'Plat principal', 10, 'Facile',   'Omelette moelleuse aux fines herbes et fromage.',                   'omelette.jpg'),
('Tajine de poulet',          'Plat principal', 75, 'Difficile','Tajine traditionnel au poulet, citron confit et olives.',           'tajine.jpg'),
('Quiche Lorraine',           'Plat principal', 60, 'Moyen',    'Quiche avec lardons, crème fraîche et emmental râpé.',             'quiche.jpg');

-- ============================================
-- DONNÉES : contacts (6 enregistrements)
-- ============================================
INSERT INTO contacts (nom, email, sujet, message, statut) VALUES
('Ahmed Ben Ali',    'ahmed.benali@email.com',   'Question recette',           'Bonjour, j\'aimerais savoir si vous avez des recettes végétariennes ?',        'lu'),
('Sara Mansour',     'sara.mansour@gmail.com',   'Suggestion d\'amélioration', 'Votre site est excellent ! Je suggère d\'ajouter un calculateur de calories.', 'repondu'),
('Mohamed Trabelsi', 'med.trabelsi@outlook.com', 'Collaboration',              'Je suis chef cuisinier et je souhaiterais collaborer avec votre équipe.',       'nouveau'),
('Lina Chaouachi',   'lina.ch@yahoo.fr',         'Problème technique',         'Le formulaire de questionnaire ne fonctionne pas sur mobile.',                  'lu'),
('Yassine Dridi',    'y.dridi@email.tn',         'Demande de partenariat',     'Nous représentons une épicerie bio et souhaitons un partenariat.',              'nouveau'),
('Nour Hamdi',       'nour.hamdi@gmail.com',     'Félicitations',              'Bravo pour votre initiative anti-gaspillage, c\'est vraiment inspirant !',      'repondu');
-- Table propositions
