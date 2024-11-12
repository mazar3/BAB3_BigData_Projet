USE bab3_bigdata_projet;

CREATE TABLE Utilisateur (
     idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
     Nom VARCHAR(50),
     Prenom VARCHAR(50),
     Telephone VARCHAR(10) NOT NULL,
     Email VARCHAR(250),
     idAdresse INT,
     idRole INT,
     idProjet INT,
     idCommande INT
);
ALTER TABLE Utilisateur ADD CONSTRAINT unique_email UNIQUE (Email);

CREATE TABLE Adresse(
    idAdresse INT AUTO_INCREMENT PRIMARY KEY,
    Rue VARCHAR(20),
    Numero INT,
    Boite VARCHAR(10),
    CodePostal INT,
    Ville VARCHAR(50),
    Pays VARCHAR(50),
    idUtilisateur INT,
    CONSTRAINT lien_utilisateur_adresse FOREIGN KEY (idAdresse) REFERENCES Utilisateur (idAdresse)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Role(
    idRole INT AUTO_INCREMENT PRIMARY KEY,
    Description VARCHAR(250),
    idUtilisateur INT,
    CONSTRAINT lien_utilisateur_role FOREIGN KEY (idRole) REFERENCES Utilisateur (idRole)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Projet(
    idProjet INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    Date_Debut DATE,
    Date_Fin DATE,
    Statut VARCHAR(50),
    idUtilisateur INT,
    idDevis INT,
    idContrat INT,
    idService INT,
    CONSTRAINT lien_utilisateur_projet FOREIGN KEY (idProjet) REFERENCES Utilisateur (idProjet)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Contrat(
    idContrat INT AUTO_INCREMENT PRIMARY KEY,
    Date_Signature DATE,
    Date_Fin DATE,
    idClause INT,
    CONSTRAINT lien_contrat_projet FOREIGN KEY (idContrat) REFERENCES Projet (idContrat)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Clause(
    idClause INT AUTO_INCREMENT PRIMARY KEY,
    Description VARCHAR(255) NOT NULL,
    CONSTRAINT lien_contrat_clause FOREIGN KEY (idClause) REFERENCES Contrat (idClause)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Devis(
    idDevis INT AUTO_INCREMENT PRIMARY KEY,
    Date_Devis DATE,
    Statut VARCHAR(50),
    CONSTRAINT lien_projet_devis FOREIGN KEY (idDevis) REFERENCES Projet (idDevis)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Service(
    idService INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    Description VARCHAR(255),
    Tarif_Horaire FLOAT,
    idTypeService INT,
    CONSTRAINT lien_projet_service FOREIGN KEY (idService) REFERENCES Projet (idService)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE TypeService(
    idTypeService INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    CONSTRAINT lien_service_typeservice FOREIGN KEY (idTypeService) REFERENCES Service (idTypeService)
);

CREATE TABLE Commande(
    idCommande INT AUTO_INCREMENT PRIMARY KEY,
    Date_Commande DATE,
    Statut VARCHAR(50),
    idUtilisateur INT,
    idFacture INT,
    CONSTRAINT lien_utlisateur_commande FOREIGN KEY (idCommande) REFERENCES Utilisateur (idCommande)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Facture(
    idFacture INT AUTO_INCREMENT PRIMARY KEY,
    Date_Facture DATE,
    Statut VARCHAR(50),
    CONSTRAINT lien_commande_facture FOREIGN KEY (idFacture) REFERENCES Commande (idFacture)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Commande_produit(
    idCommande INT,
    idProduit INT,
    CONSTRAINT CLE_PRI PRIMARY KEY (idCommande,idProduit),
    CONSTRAINT lien_commande_commandeproduit FOREIGN KEY (idCommande) REFERENCES Commande (idCommande)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Produit(
    idProduit INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    Description VARCHAR(255),
    Prix INT,
    Stock INT,
    idTypeProduit INT,
    idFournisseur INT,
    CONSTRAINT lien_commandeproduit_produit FOREIGN KEY (idProduit) REFERENCES Commande_produit(idProduit)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Type_produit(
    idTypeProduit INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    CONSTRAINT lien_produit_typeproduit FOREIGN KEY (idTypeProduit) REFERENCES Produit (idTypeProduit)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE Fournisseur(
    idFournisseur INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    Adresse VARCHAR(150),
    Telephone VARCHAR(10) NOT NULL,
    Email VARCHAR(250),
    CONSTRAINT lien_produit_fournisseur FOREIGN KEY (idFournisseur) REFERENCES Produit (idFournisseur)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);