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

-- 1. Création des Tables Indépendantes en Premier
CREATE TABLE Role(
                     idRole INT AUTO_INCREMENT PRIMARY KEY,
                     Description VARCHAR(250)
) ENGINE=InnoDB;

CREATE TABLE TypeService(
                            idTypeService INT AUTO_INCREMENT PRIMARY KEY,
                            Nom VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE Type_produit(
                             idTypeProduit INT AUTO_INCREMENT PRIMARY KEY,
                             Nom VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE Fournisseur(
                            idFournisseur INT AUTO_INCREMENT PRIMARY KEY,
                            Nom VARCHAR(50),
                            Adresse VARCHAR(150),
                            Telephone VARCHAR(15) NOT NULL,
                            Email VARCHAR(250)
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

-- 2. Création des Tables Dépendantes

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
    -- Suppression de idService ici
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
