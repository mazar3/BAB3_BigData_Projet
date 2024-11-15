USE bab3_bigdata_projet;

CREATE TABLE Utilisateur (
     idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
     Nom VARCHAR(50),
     Prenom VARCHAR(50),
     Telephone VARCHAR(15) NOT NULL,
     Email VARCHAR(250),
     idAdresse INT,
     idRole INT,
     idProjet INT,
     idCommande INT,
     CONSTRAINT unique_email UNIQUE (Email),
     CONSTRAINT lien_utilisateur_role FOREIGN KEY (idRole) REFERENCES Role (idRole)
         ON DELETE SET NULL
         ON UPDATE CASCADE
);

CREATE TABLE Adresse(
    idAdresse INT AUTO_INCREMENT PRIMARY KEY,
    Rue VARCHAR(20),
    Numero INT,
    Boite VARCHAR(10),
    CodePostal INT,
    Ville VARCHAR(50),
    Pays VARCHAR(50),
    idUtilisateur INT,
    CONSTRAINT lien_utilisateur_adresse FOREIGN KEY (idUtilisateur) REFERENCES Utilisateur (idUtilisateur)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE Role(
    idRole INT AUTO_INCREMENT PRIMARY KEY,
    Description VARCHAR(250),
);

CREATE TABLE Projet(
    idProjet INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    Date_Debut DATE,
    Date_Fin DATE,
    Statut VARCHAR(50),
    idUtilisateur INT,
    idPanier INT,
    idContrat INT,
    idService INT,
    CONSTRAINT lien_utilisateur_projet FOREIGN KEY (idUtilisateur) REFERENCES Utilisateur (idUtilisateur)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT lien_projet_contrat FOREIGN KEY (idContrat) REFERENCES Contrat (idContrat)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT lien_projet_panier FOREIGN KEY (idPanier) REFERENCES Devis (idPanier)
        ON DELETE SET NULL
        ON UPDATE CASCADE

);

CREATE TABLE Contrat(
    idContrat INT AUTO_INCREMENT PRIMARY KEY,
    Date_Signature DATE,
    Date_Fin DATE,
    idClause INT,
    CONSTRAINT lien_contrat_clause FOREIGN KEY (idClause) REFERENCES Clause (idClause)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE Clause(
    idClause INT AUTO_INCREMENT PRIMARY KEY,
    Description VARCHAR(255) NOT NULL
);

CREATE TABLE Panier(
    idPanier INT AUTO_INCREMENT PRIMARY KEY,
    Date_Panier DATE,
    Statut VARCHAR(50)
);

CREATE TABLE Service(
    idService INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    Description VARCHAR(255),
    Tarif_Horaire FLOAT,
    idTypeService INT,
    CONSTRAINT lien_projet_service FOREIGN KEY (idService) REFERENCES Projet (idService)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT lien_service_typeservice FOREIGN KEY (idTypeService) REFERENCES TypeService (idTypeService)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE TypeService(
    idTypeService INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50)
);

CREATE TABLE Commande(
    idCommande INT AUTO_INCREMENT PRIMARY KEY,
    Date_Commande DATE,
    Statut VARCHAR(50),
    idUtilisateur INT,
    idFacture INT,
    CONSTRAINT lien_utlisateur_commande FOREIGN KEY (idCommande) REFERENCES Utilisateur (idCommande)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE Facture(
    idFacture INT AUTO_INCREMENT PRIMARY KEY,
    Date_Facture DATE,
    Statut VARCHAR(50),
    CONSTRAINT lien_commande_facture FOREIGN KEY (idFacture) REFERENCES Commande (idFacture)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE Commande_produit(
    idCommande INT,
    idProduit INT,
    CONSTRAINT CLE_PRI PRIMARY KEY (idCommande,idProduit),
    CONSTRAINT lien_commande_commandeproduit FOREIGN KEY (idCommande) REFERENCES Commande (idCommande)
        ON DELETE SET NULL
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
    CONSTRAINT lien_produit_typeproduit FOREIGN KEY (idTypeProduit) REFERENCES Type_produit (idTypeProduit),
    CONSTRAINT lien_produit_fournisseur FOREIGN KEY (idFournisseur) REFERENCES Fournisseur (idFournisseur)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE Type_produit(
    idTypeProduit INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50)
);

CREATE TABLE Fournisseur(
    idFournisseur INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50),
    Adresse VARCHAR(150),
    Telephone VARCHAR(15) NOT NULL,
    Email VARCHAR(250)
);