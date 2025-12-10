-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 28 nov. 2025 à 09:50
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projetfinal`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--
create database projetfinal;
use projetfinal;


CREATE TABLE `admin` (
  `idAdmin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `chat`
--

CREATE TABLE `chat` (
  `idChat` int(11) NOT NULL,
  `idClient` int(11) NOT NULL,
  `idIntervenant` int(11) NOT NULL,
  `idIntervention` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `idClient` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `idCommentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `idClient` int(11) NOT NULL,
  `idIntervenant` int(11) NOT NULL,
  `idIntervention` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `critere`
--

CREATE TABLE `critere` (
  `idcritere` int(11) NOT NULL,
  `nom` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluation`
--

CREATE TABLE `evaluation` (
  `idEvaluation` int(11) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `typeauteur` enum('Client','Intervenant') DEFAULT NULL,
  `idCritere` int(11) DEFAULT NULL,
  `idIntervention` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `facture`
--

CREATE TABLE `facture` (
  `numFacture` int(11) NOT NULL,
  `TTC` decimal(10,2) DEFAULT NULL,
  `idIntervention` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `intervenant`
--

CREATE TABLE `intervenant` (
  `idIntervenant` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `intervenantinterventions`
--

CREATE TABLE `intervenantinterventions` (
  `idIntervenant` int(11) NOT NULL,
  `idIntervention` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `intervenantservices`
--

CREATE TABLE `intervenantservices` (
  `id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `idService` int(11) NOT NULL,
  `idIntervenant` int(11) DEFAULT NULL,
  `idJustificatif` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `intervenanttache`
--

CREATE TABLE `intervenanttache` (
  `id` int(11) NOT NULL,
  `idIntervenant` int(11) DEFAULT NULL,
  `idTache` int(11) DEFAULT NULL,
  `prixTache` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `intervention`
--

CREATE TABLE `intervention` (
  `idIntervention` int(11) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `dateIntervention` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idClient` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `interventiontaches`
--

CREATE TABLE `interventiontaches` (
  `idTache` int(11) NOT NULL,
  `idIntervention` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `justificatif`
--

CREATE TABLE `justificatif` (
  `idJustificatif` int(11) NOT NULL,
  `cheminFichier` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `idMateriel` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `idMessage` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `idEmetteur` int(11) NOT NULL,
  `idChat` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `partitiontable`
--

CREATE TABLE `partitiontable` (
  `idPartition` int(11) NOT NULL,
  `idIntervenant` int(11) DEFAULT NULL,
  `jour` date NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `heureDepart` time DEFAULT NULL,
  `heureFin` time DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

CREATE TABLE `service` (
  `idService` int(11) NOT NULL,
  `nomService` varchar(100) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tache`
--

CREATE TABLE `tache` (
  `idTache` int(11) NOT NULL,
  `nomTache` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `idService` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `idUtilisateur` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `photoPath` varchar(255) DEFAULT NULL,
  `googleUid` varchar(100) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`idAdmin`);

--
-- Index pour la table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`idChat`),
  ADD KEY `idClient` (`idClient`),
  ADD KEY `idIntervenant` (`idIntervenant`),
  ADD KEY `idIntervention` (`idIntervention`);

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`idClient`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`idCommentaire`),
  ADD KEY `idClient` (`idClient`),
  ADD KEY `idIntervenant` (`idIntervenant`),
  ADD KEY `idIntervention` (`idIntervention`);

--
-- Index pour la table `critere`
--
ALTER TABLE `critere`
  ADD PRIMARY KEY (`idcritere`);

--
-- Index pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD KEY `idIntervention` (`idIntervention`),
  ADD KEY `idCritere` (`idCritere`);

--
-- Index pour la table `facture`
--
ALTER TABLE `facture`
  ADD PRIMARY KEY (`numFacture`),
  ADD KEY `idIntervention` (`idIntervention`);

--
-- Index pour la table `intervenant`
--
ALTER TABLE `intervenant`
  ADD PRIMARY KEY (`idIntervenant`);

--
-- Index pour la table `intervenantinterventions`
--
ALTER TABLE `intervenantinterventions`
  ADD PRIMARY KEY (`idIntervenant`,`idIntervention`),
  ADD KEY `idIntervention` (`idIntervention`);

--
-- Index pour la table `intervenantservices`
--
ALTER TABLE `intervenantservices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idService` (`idService`),
  ADD KEY `idIntervenant` (`idIntervenant`),
  ADD KEY `idJustificatif` (`idJustificatif`);

--
-- Index pour la table `intervenanttache`
--
ALTER TABLE `intervenanttache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idIntervenant` (`idIntervenant`),
  ADD KEY `idTache` (`idTache`);

--
-- Index pour la table `intervention`
--
ALTER TABLE `intervention`
  ADD PRIMARY KEY (`idIntervention`),
  ADD KEY `idClient` (`idClient`);

--
-- Index pour la table `interventiontaches`
--
ALTER TABLE `interventiontaches`
  ADD PRIMARY KEY (`idTache`,`idIntervention`),
  ADD KEY `idIntervention` (`idIntervention`);

--
-- Index pour la table `justificatif`
--
ALTER TABLE `justificatif`
  ADD PRIMARY KEY (`idJustificatif`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`idMessage`),
  ADD KEY `idEmetteur` (`idEmetteur`),
  ADD KEY `idChat` (`idChat`);

--
-- Index pour la table `partitiontable`
--
ALTER TABLE `partitiontable`
  ADD PRIMARY KEY (`idPartition`),
  ADD KEY `idIntervenant` (`idIntervenant`);

--
-- Index pour la table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`idService`);

--
-- Index pour la table `tache`
--
ALTER TABLE `tache`
  ADD PRIMARY KEY (`idTache`),
  ADD KEY `idService` (`idService`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`idUtilisateur`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `googleUid` (`googleUid`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `chat`
--
ALTER TABLE `chat`
  MODIFY `idChat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `idCommentaire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `facture`
--
ALTER TABLE `facture`
  MODIFY `numFacture` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `intervenantservices`
--
ALTER TABLE `intervenantservices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `intervenanttache`
--
ALTER TABLE `intervenanttache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `intervention`
--
ALTER TABLE `intervention`
  MODIFY `idIntervention` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `justificatif`
--
ALTER TABLE `justificatif`
  MODIFY `idJustificatif` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `idMessage` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `partitiontable`
--
ALTER TABLE `partitiontable`
  MODIFY `idPartition` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `service`
--
ALTER TABLE `service`
  MODIFY `idService` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tache`
--
ALTER TABLE `tache`
  MODIFY `idTache` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `idUtilisateur` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`idAdmin`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`idIntervenant`) REFERENCES `intervenant` (`idIntervenant`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_ibfk_3` FOREIGN KEY (`idIntervention`) REFERENCES `intervention` (`idIntervention`);

--
-- Contraintes pour la table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`idIntervenant`) REFERENCES `intervenant` (`idIntervenant`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentaire_ibfk_3` FOREIGN KEY (`idIntervention`) REFERENCES `intervention` (`idIntervention`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD CONSTRAINT `evaluation_ibfk_1` FOREIGN KEY (`idIntervention`) REFERENCES `intervention` (`idIntervention`),
  ADD CONSTRAINT `evaluation_ibfk_2` FOREIGN KEY (`idCritere`) REFERENCES `critere` (`idcritere`);

--
-- Contraintes pour la table `facture`
--
ALTER TABLE `facture`
  ADD CONSTRAINT `facture_ibfk_1` FOREIGN KEY (`idIntervention`) REFERENCES `intervention` (`idIntervention`) ON DELETE CASCADE;

--
-- Contraintes pour la table `intervenant`
--
ALTER TABLE `intervenant`
  ADD CONSTRAINT `intervenant_ibfk_1` FOREIGN KEY (`idIntervenant`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `intervenantinterventions`
--
ALTER TABLE `intervenantinterventions`
  ADD CONSTRAINT `intervenantinterventions_ibfk_1` FOREIGN KEY (`idIntervenant`) REFERENCES `intervenant` (`idIntervenant`) ON DELETE CASCADE,
  ADD CONSTRAINT `intervenantinterventions_ibfk_2` FOREIGN KEY (`idIntervention`) REFERENCES `intervention` (`idIntervention`) ON DELETE CASCADE;

--
-- Contraintes pour la table `intervenantservices`
--
ALTER TABLE `intervenantservices`
  ADD CONSTRAINT `intervenantservices_ibfk_1` FOREIGN KEY (`idService`) REFERENCES `service` (`idService`) ON DELETE CASCADE,
  ADD CONSTRAINT `intervenantservices_ibfk_2` FOREIGN KEY (`idIntervenant`) REFERENCES `intervenant` (`idIntervenant`) ON DELETE SET NULL,
  ADD CONSTRAINT `intervenantservices_ibfk_3` FOREIGN KEY (`idJustificatif`) REFERENCES `justificatif` (`idJustificatif`);

--
-- Contraintes pour la table `intervenanttache`
--
ALTER TABLE `intervenanttache`
  ADD CONSTRAINT `intervenanttache_ibfk_1` FOREIGN KEY (`idIntervenant`) REFERENCES `intervenant` (`idIntervenant`),
  ADD CONSTRAINT `intervenanttache_ibfk_2` FOREIGN KEY (`idTache`) REFERENCES `tache` (`idTache`);

--
-- Contraintes pour la table `intervention`
--
ALTER TABLE `intervention`
  ADD CONSTRAINT `intervention_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`);

--
-- Contraintes pour la table `interventiontaches`
--
ALTER TABLE `interventiontaches`
  ADD CONSTRAINT `interventiontaches_ibfk_1` FOREIGN KEY (`idTache`) REFERENCES `tache` (`idTache`) ON DELETE CASCADE,
  ADD CONSTRAINT `interventiontaches_ibfk_2` FOREIGN KEY (`idIntervention`) REFERENCES `intervention` (`idIntervention`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`idEmetteur`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`idChat`) REFERENCES `chat` (`idChat`);

--
-- Contraintes pour la table `partitiontable`
--
ALTER TABLE `partitiontable`
  ADD CONSTRAINT `partitiontable_ibfk_1` FOREIGN KEY (`idIntervenant`) REFERENCES `intervenant` (`idIntervenant`);

--
-- Contraintes pour la table `tache`
--
ALTER TABLE `tache`
  ADD CONSTRAINT `tache_ibfk_1` FOREIGN KEY (`idService`) REFERENCES `service` (`idService`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
