USE bab3_bigdata_projet;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS
    Commande_produit,
    Produit,
    Commande,
    Facture,
    Type_produit,
    Fournisseur,
    Panier,
    Clause,
    Contrat,
    Service,
    TypeService,
    Panier,
    Projet,
    Role,
    Adresse,
    Utilisateur;

SET FOREIGN_KEY_CHECKS = 1;

--------------------------------------------------------------------------------------------------------------------------

USE bab3_bigdata_projet;

CREATE TABLE TypeService(
                            idTypeService INT AUTO_INCREMENT PRIMARY KEY,
                            Nom VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE Type_produit(
                             idTypeProduit INT AUTO_INCREMENT PRIMARY KEY,
                             Nom VARCHAR(50)
) ENGINE=InnoDB;



CREATE TABLE Clause(
                       idClause INT AUTO_INCREMENT PRIMARY KEY,
                       Description VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Panier(
                       idPanier INT AUTO_INCREMENT PRIMARY KEY,
                       Date_Panier DATE,
                       Montant FLOAT,
                       Statut VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE Role(
                     idRole INT AUTO_INCREMENT PRIMARY KEY,
                     Description VARCHAR(250)
) ENGINE=InnoDB;

CREATE TABLE Fournisseur(
                            idFournisseur INT AUTO_INCREMENT PRIMARY KEY,
                            Nom VARCHAR(50),
                            Adresse VARCHAR(150),
                            Telephone VARCHAR(15) NOT NULL,
                            Email VARCHAR(250)
) ENGINE=InnoDB;

CREATE TABLE Utilisateur (
                             idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
                             Nom VARCHAR(50),
                             Prenom VARCHAR(50),
                             Telephone VARCHAR(15) NOT NULL,
                             Email VARCHAR(250),
                             Mot_De_Passe_Hash VARCHAR(250),
                             idAdresse INT NULL,
                             idRole INT NULL,
                             idProjet INT NULL,
                             idCommande INT NULL,
                             CONSTRAINT unique_email UNIQUE (Email),
                             CONSTRAINT lien_utilisateur_role FOREIGN KEY (idRole) REFERENCES Role (idRole)
                                 ON DELETE SET NULL
                                 ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Contrat(
                        idContrat INT AUTO_INCREMENT PRIMARY KEY,
                        Date_Signature DATE,
                        Date_Fin DATE,
                        idClause INT NULL,
                        CONSTRAINT lien_contrat_clause FOREIGN KEY (idClause) REFERENCES Clause (idClause)
                            ON DELETE SET NULL
                            ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Projet(
                       idProjet INT AUTO_INCREMENT PRIMARY KEY,
                       Nom VARCHAR(50),
                       Date_Debut DATE,
                       Date_Fin DATE,
                       Statut VARCHAR(50),
                       idUtilisateur INT NULL,
                       idPanier INT NULL,
                       idContrat INT NULL,
                       CONSTRAINT lien_utilisateur_projet FOREIGN KEY (idUtilisateur) REFERENCES Utilisateur (idUtilisateur)
                           ON DELETE SET NULL
                           ON UPDATE CASCADE,
                       CONSTRAINT lien_projet_contrat FOREIGN KEY (idContrat) REFERENCES Contrat (idContrat)
                           ON DELETE SET NULL
                           ON UPDATE CASCADE,
                       CONSTRAINT lien_projet_panier FOREIGN KEY (idPanier) REFERENCES Panier (idPanier)
                           ON DELETE SET NULL
                           ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Service(
                        idService INT AUTO_INCREMENT PRIMARY KEY,
                        Nom VARCHAR(50),
                        Description VARCHAR(255),
                        Tarif_Horaire FLOAT,
                        idTypeService INT NULL,
                        idProjet INT NULL, -- Ajout de idProjet pour référencer Projet
                        CONSTRAINT lien_service_typeservice FOREIGN KEY (idTypeService) REFERENCES TypeService (idTypeService)
                            ON DELETE SET NULL
                            ON UPDATE CASCADE,
                        CONSTRAINT lien_service_projet FOREIGN KEY (idProjet) REFERENCES Projet (idProjet)
                            ON DELETE SET NULL
                            ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Adresse(
                        idAdresse INT AUTO_INCREMENT PRIMARY KEY,
                        Rue VARCHAR(20),
                        Numero INT,
                        Boite VARCHAR(10),
                        CodePostal INT,
                        Ville VARCHAR(50),
                        Pays VARCHAR(50),
                        idUtilisateur INT NULL,
                        CONSTRAINT lien_utilisateur_adresse FOREIGN KEY (idUtilisateur) REFERENCES Utilisateur (idUtilisateur)
                            ON DELETE SET NULL
                            ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Facture(
                        idFacture INT AUTO_INCREMENT PRIMARY KEY,
                        Date_Facture DATE,
                        Montant FLOAT,
                        Statut VARCHAR(50),
                        idCommande INT NULL
) ENGINE=InnoDB;

CREATE TABLE Commande(
                         idCommande INT AUTO_INCREMENT PRIMARY KEY,
                         Date_Commande DATE,
                         Statut VARCHAR(50),
                         idUtilisateur INT NULL,
                         idFacture INT NULL,
                         CONSTRAINT lien_commande_facture FOREIGN KEY (idFacture) REFERENCES Facture (idFacture)
                             ON DELETE SET NULL
                             ON UPDATE CASCADE,
                         CONSTRAINT lien_commande_utilisateur FOREIGN KEY (idUtilisateur) REFERENCES Utilisateur (idUtilisateur)
                             ON DELETE SET NULL
                             ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Produit(
                        idProduit INT AUTO_INCREMENT PRIMARY KEY,
                        Nom VARCHAR(50),
                        Description VARCHAR(255),
                        Prix INT,
                        Stock INT,
                        idTypeProduit INT NULL,
                        idFournisseur INT NULL,
                        CONSTRAINT lien_produit_typeproduit FOREIGN KEY (idTypeProduit) REFERENCES Type_produit (idTypeProduit)
                            ON DELETE SET NULL
                            ON UPDATE CASCADE,
                        CONSTRAINT lien_produit_fournisseur FOREIGN KEY (idFournisseur) REFERENCES Fournisseur (idFournisseur)
                            ON DELETE SET NULL
                            ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Commande_produit(
                                 idCommande INT,
                                 idProduit INT,
                                 Quantite INT,
                                 CONSTRAINT CLE_PRI PRIMARY KEY (idCommande, idProduit),
                                 CONSTRAINT lien_commandeproduit_commande FOREIGN KEY (idCommande) REFERENCES Commande (idCommande)
                                     ON DELETE CASCADE
                                     ON UPDATE CASCADE,
                                 CONSTRAINT lien_commandeproduit_produit FOREIGN KEY (idProduit) REFERENCES Produit (idProduit)
                                     ON DELETE CASCADE
                                     ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Commentaires (
                              idCommentaire INT AUTO_INCREMENT PRIMARY KEY,
                              idProjet INT NOT NULL,
                              idUtilisateur INT NOT NULL,
                              Commentaire TEXT NOT NULL,
                              Date_Commentaire DATETIME DEFAULT CURRENT_TIMESTAMP,
                              CONSTRAINT fk_commentaires_projet FOREIGN KEY (idProjet) REFERENCES Projet(idProjet) ON DELETE CASCADE,
                              CONSTRAINT fk_commentaires_utilisateur FOREIGN KEY (idUtilisateur) REFERENCES Utilisateur(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Projet_manager (
                                idProjet INT NOT NULL,
                                idUtilisateur INT NOT NULL,
                                PRIMARY KEY (idProjet, idUtilisateur),
                                CONSTRAINT fk_projet_manager_projet FOREIGN KEY (idProjet) REFERENCES Projet(idProjet) ON DELETE CASCADE ON UPDATE CASCADE,
                                CONSTRAINT fk_projet_manager_utilisateur FOREIGN KEY (idUtilisateur) REFERENCES Utilisateur(idUtilisateur) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


--------------------------------------------------------------------------------------------------------------------------


-- ------------------------------------------------
-- INSERTS pour la table Role
-- ------------------------------------------------
INSERT INTO Role (Description)
VALUES
    ("Administrateur"),
    ("Responsable de projet"),
    ("Client");

-- ------------------------------------------------
-- INSERTS pour la table TypeService
-- ------------------------------------------------
INSERT INTO TypeService (Nom)
VALUES
    ("Installation"),
    ("Maintenance"),
    ("Consultance"),
    ("Formation"),
    ("Développement");

-- ------------------------------------------------
-- INSERTS pour la table Type_produit
-- ------------------------------------------------
INSERT INTO Type_produit (Nom)
VALUES
    ("Informatique"),
    ("Logiciels"),
    ("Bureautique"),
    ("Accessoires"),
    ("Réseaux");

-- ------------------------------------------------
-- INSERTS pour la table Fournisseur
-- ------------------------------------------------
INSERT INTO Fournisseur (Nom, Adresse, Telephone, Email)
VALUES
    ("Fournisseur ABC", "123 Rue Exemple, 1000 Bruxelles", "0123456789", "contact@fournisseurabc.be"),
    ("TechPlanet", "45 Boulevard Tech, 1000 Bruxelles", "0987654321", "sales@techplanet.com"),
    ("GlobalSoft", "200 Avenue Logiciel, 1200 Bruxelles", "0246801357", "info@globalsoft.org"),
    ("MatLog SA", "10 Rue du Matériel, 4000 Liège", "0471234567", "vente@matlog.be");

-- ------------------------------------------------
-- INSERTS pour la table Clause
-- ------------------------------------------------
INSERT INTO Clause (Description)
VALUES
    ("Le Client s’engage à respecter les conditions de paiement dans les délais."),
    ("Tout dépassement de délai donnera lieu à une pénalité."),
    ("Le Fournisseur s’engage à garantir les produits pendant une durée de 2 ans.");

-- ------------------------------------------------
-- INSERTS pour la table Panier
-- ------------------------------------------------
INSERT INTO Panier (Date_Panier, Montant, Statut)
VALUES
    ("2023-09-01", 0, "En cours"),
    ("2023-09-02", 0, "En cours"),
    ("2023-09-03", 0, "En cours"),
    ("2023-09-04", 0, "En cours"),
    ("2023-09-05", 0, "En cours");

-- ------------------------------------------------
-- UPDATE pour la table Utilisateur
-- ------------------------------------------------
UPDATE utilisateur
SET idRole = 1;
WHERE idUtilisateur = 1;

-- ------------------------------------------------
-- INSERTS pour la table Contrat
-- ------------------------------------------------
INSERT INTO Contrat (Date_Signature, Date_Fin, idClause)
VALUES
    ("2023-09-10", "2023-12-10", 1),
    ("2023-09-15", "2024-01-15", 2),
    ("2023-09-20", "2024-02-20", 3);

-- ------------------------------------------------
-- INSERTS pour la table Projet
-- ------------------------------------------------
INSERT INTO Projet
(Nom, Date_Debut, Date_Fin, Statut, idUtilisateur, idPanier, idContrat)
VALUES
    ("Projet Site Web", "2023-09-01", "2023-11-30", "En cours", 2, 1, 1),  -- Jane Smith (Resp. projet)
    ("Projet Migration Serveur", "2023-09-05", "2023-12-01", "En cours", 5, 2, 2), -- Max Taylor (Resp. projet)
    ("Projet Formation Dev", "2023-09-10", "2024-01-10", "En cours", 9, 3, 3), -- David Wang (Resp. projet)
    ("Projet Chatbot IA", "2023-09-12", "2024-03-12", "En cours", 2, 4, NULL), -- Jane Smith
    ("Projet Cloud", "2023-09-20", "2024-01-15", "En cours", 5, 5, NULL); -- Max Taylor

-- ------------------------------------------------
-- INSERTS pour la table Service
-- ------------------------------------------------
INSERT INTO Service
(Nom, Description, Tarif_Horaire, idTypeService, idProjet)
VALUES
    ("Installation Serveur", "Installation initiale du serveur physique", 45.0, 1, 2),
    ("Maintenance Infrastructure", "Supervision et maintenance du parc informatique", 40.0, 2, 1),
    ("Formation DevOps", "Formation sur les outils DevOps", 60.0, 4, 3),
    ("Consultance IA", "Conseils et audit pour l'IA dans le chatbot", 70.0, 3, 4),
    ("Développement Cloud", "Mise en place d'une solution cloud", 50.0, 5, 5);

-- ------------------------------------------------
-- INSERTS pour la table Adresse
-- ------------------------------------------------
INSERT INTO Adresse
(Rue, Numero, Boite, CodePostal, Ville, Pays, idUtilisateur)
VALUES
    ("Rue Principale", 12, "A", 1000, "Bruxelles", "Belgique", 1),  -- Admin
    ("Avenue du Centre", 45, NULL, 1000, "Bruxelles", "Belgique", 2),
    ("Boulevard des Fleurs", 78, "B", 4000, "Liège", "Belgique", 4),
    ("Rue de la Gare", 10, NULL, 5000, "Namur", "Belgique", 10);

-- ------------------------------------------------
-- INSERTS pour la table Facture
-- ------------------------------------------------
INSERT INTO Facture
(Date_Facture, Montant, Statut, idCommande)
VALUES
    ("2023-10-01", 1500.00, "Non Payée", NULL),
    ("2023-10-05", 2700.50, "Non Payée", NULL),
    ("2023-10-10", 999.99, "Non Payée", NULL);

-- ------------------------------------------------
-- INSERTS pour la table Commande
-- ------------------------------------------------
INSERT INTO Commande
(Date_Commande, Statut, idUtilisateur, idFacture)
VALUES
    ("2023-10-01", "En cours", 4, 1), -- Julie Adams (Client)
    ("2023-10-02", "En cours", 8, 2), -- Maria Rodriguez (Client)
    ("2023-10-03", "En cours", 10, 3); -- Lucie Dupont (Client)

-- ------------------------------------------------
-- INSERTS pour la table Produit
-- ------------------------------------------------
INSERT INTO Produit
(Nom, Description, Prix, Stock, idTypeProduit, idFournisseur)
VALUES
    ('PC Portable', 'PC portable pour taches communes', 700, 10, 1, 1),
    ('PC Portable Pro', 'PC portable haute performance', 1200, 50, 1, 1),
    ('Windows Server Licence', 'Licence Windows Server 2022', 800, 30, 2, 2),
    ('Pack Bureautique', 'Suite complète de bureautique', 200, 100, 2, 3),
    ('Souris Gaming', 'Souris optique haute précision', 40, 75, 4, 3),
    ('Switch Réseau 24 ports', 'Switch Gigabit 24 ports', 150, 20, 5, 3),
    ('PC Bureau Standard', 'Ordinateur fixe entrée de gamme', 500, 60, 1, 4),
    ('Clavier Mécanique', 'Clavier mécanique haute durabilité', 70, 45, 4, 3),
    ('Logiciel CRM', 'CRM pour gestion de clients', 300, 25, 2, 2),
    ('Casque Audio', 'Casque pour visioconférence', 25, 80, 4, 1),
    ('Routeur Wi-Fi Pro', 'Routeur Dual-Band haute performance', 120, 15, 5, 3),
    ('Bureau', 'Bureau en bois massif pour ordinateur fixe', 700, 20, 3, 4),
    ('Chaise bureau', 'Chaise ergonomique pour bureau', 300, 50, 3, 4);

-- ------------------------------------------------
-- INSERTS pour la table Commande_produit
-- ------------------------------------------------
INSERT INTO Commande_produit (idCommande, idProduit, Quantite)
VALUES
    (1, 1, 2), -- Julie Adams commande 2 PC Portable Pro
    (1, 2, 1), -- + 1 Licence Windows Server
    (2, 3, 3), -- Maria commande 3 Packs Bureautique
    (2, 4, 2), -- + 2 Souris Gaming
    (3, 7, 1); -- Lucie commande 1 Clavier Mécanique

-- ------------------------------------------------
-- INSERTS pour la table Commentaires
-- ------------------------------------------------
INSERT INTO Commentaires
(idProjet, idUtilisateur, Commentaire, Date_Commentaire)
VALUES
    (1, 4, "Merci pour l'avancement rapide du projet !", "2023-10-03 14:20:00"),
    (1, 2, "Pas de souci, on avance comme prévu.", "2023-10-03 15:40:00"),
    (2, 6, "Pensez à ajouter un service de maintenance supplémentaire.", "2023-10-04 09:10:00"),
    (3, 4, "Quelle est la date finale prévue ?", "2023-10-04 10:00:00");

-- ------------------------------------------------
-- INSERTS pour la table Projet_manager
-- ------------------------------------------------
INSERT INTO Projet_manager
(idProjet, idUtilisateur)
VALUES
    (1, 2),  -- Projet #1 -> Jane Smith
    (2, 5),  -- Projet #2 -> Max Taylor
    (3, 9),  -- Projet #3 -> David Wang
    (4, 2),  -- Projet #4 -> Jane Smith
    (5, 5);  -- Projet #5 -> Max Taylor

-- ------------------------------------------------
-- EXEMPLE (OPTIONNEL) : lier factures et commandes
-- ------------------------------------------------
-- UPDATE Facture SET idCommande = 1 WHERE idFacture = 1;
-- UPDATE Facture SET idCommande = 2 WHERE idFacture = 2;
-- ...

-- FIN DU SCRIPT
