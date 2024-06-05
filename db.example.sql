CREATE TABLE etudiant(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    matricule VARCHAR(10) NOT NULL UNIQUE
);

CREATE TABLE devoir(
    id INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id INT NOT NULL,
    devoir_type VARCHAR(100),
    fichier VARCHAR(100),
    date_envoi datetime default current_timestamp
);

CREATE TABLE admin(
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL
);

INSERT INTO admin(username, password) VALUES
('boss', 'c8a1d736fcebb50e6b53dc2c40597b30'), -- h3ll0_b055
;