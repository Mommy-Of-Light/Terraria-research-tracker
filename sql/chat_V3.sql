-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 28 avr. 2026 à 16:21
-- Version du serveur : 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Version de PHP : 8.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `chat_V3`
--

DROP DATABASE IF EXISTS `chat_V3`;
CREATE DATABASE `chat_V3`;
USE `chat_V3`;

-- --------------------------------------------------------

--
-- Structure de la table `Chat`
--

CREATE TABLE `Chat` (
  `idChat` int(11) UNSIGNED NOT NULL,
  `idCreator` int(11) UNSIGNED NOT NULL,
  `ChatName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Chat`
--

INSERT INTO `Chat` (`idChat`, `idCreator`, `ChatName`) VALUES
(1, 1, 'test');

-- --------------------------------------------------------

--
-- Structure de la table `Message`
--

CREATE TABLE `Message` (
  `idMessage` int(11) UNSIGNED NOT NULL,
  `idUser` int(11) UNSIGNED NOT NULL,
  `idTime` int(11) UNSIGNED NOT NULL,
  `Content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Message`
--

INSERT INTO `Message` (`idMessage`, `idUser`, `idTime`, `Content`) VALUES
(1, 1, 3, 'hello guys'),
(2, 2, 4, 'hi'),
(3, 1, 5, 'w'),
(4, 1, 6, 'wwe'),
(5, 1, 7, 'w'),
(6, 1, 8, 'w'),
(7, 1, 9, 'w'),
(8, 1, 10, 'w');

-- --------------------------------------------------------

--
-- Structure de la table `Player_file`
--

CREATE TABLE `Player_file` (
  `idFile` int(11) UNSIGNED NOT NULL,
  `IdUser` int(11) UNSIGNED NOT NULL,
  `fileName` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Player_file`
--

INSERT INTO `Player_file` (`idFile`, `IdUser`, `fileName`) VALUES
(39, 1, 'Creator-69e716cae2c48/player_1776752343.plr'),
(41, 1, 'Creator-69e716cae2c48/file_1777366519.plr');

-- --------------------------------------------------------

--
-- Structure de la table `Time`
--

CREATE TABLE `Time` (
  `idTime` int(11) UNSIGNED NOT NULL,
  `idChat` int(11) UNSIGNED NOT NULL,
  `TimeStamp` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Time`
--

INSERT INTO `Time` (`idTime`, `idChat`, `TimeStamp`) VALUES
(3, 1, '2026-02-03'),
(4, 1, '2026-02-03'),
(5, 1, '2026-03-03'),
(6, 1, '2026-03-03'),
(7, 1, '2026-03-03'),
(8, 1, '2026-03-03'),
(9, 1, '2026-03-03'),
(10, 1, '2026-03-17');

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE `Users` (
  `idUser` int(11) UNSIGNED NOT NULL,
  `Email` varchar(100) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `Password` varchar(256) NOT NULL,
  `pfp` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Users`
--

INSERT INTO `Users` (`idUser`, `Email`, `UserName`, `Password`, `pfp`) VALUES
(1, 'empress.mommy.of.light@gmail.com', 'Creator', '$2y$12$OHHuokViatwrGBRrvuxwa.oo46RdG6cW/b0qNT8.FxtW5hdM9MdC6', 'https://ui-avatars.com/api/?name=Creator'),
(2, 'test1@example.com', 'Test', '$2y$12$nH9of0n2saC6MlOriAHMeeTl0BGJNONMtG41qe625CIBrfTMVg4ji', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Chat`
--
ALTER TABLE `Chat`
  ADD PRIMARY KEY (`idChat`),
  ADD KEY `idCreator` (`idCreator`);

--
-- Index pour la table `Message`
--
ALTER TABLE `Message`
  ADD PRIMARY KEY (`idMessage`),
  ADD KEY `idUser` (`idUser`),
  ADD KEY `idTime` (`idTime`);

--
-- Index pour la table `Player_file`
--
ALTER TABLE `Player_file`
  ADD PRIMARY KEY (`idFile`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Index pour la table `Time`
--
ALTER TABLE `Time`
  ADD PRIMARY KEY (`idTime`),
  ADD KEY `idChat` (`idChat`);

--
-- Index pour la table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `UserName` (`UserName`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Chat`
--
ALTER TABLE `Chat`
  MODIFY `idChat` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `Message`
--
ALTER TABLE `Message`
  MODIFY `idMessage` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `Player_file`
--
ALTER TABLE `Player_file`
  MODIFY `idFile` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pour la table `Time`
--
ALTER TABLE `Time`
  MODIFY `idTime` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `Users`
--
ALTER TABLE `Users`
  MODIFY `idUser` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Chat`
--
ALTER TABLE `Chat`
  ADD CONSTRAINT `Chat_ibfk_1` FOREIGN KEY (`idCreator`) REFERENCES `Users` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Message`
--
ALTER TABLE `Message`
  ADD CONSTRAINT `Message_ibfk_1` FOREIGN KEY (`idTime`) REFERENCES `Time` (`idTime`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Message_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `Users` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Player_file`
--
ALTER TABLE `Player_file`
  ADD CONSTRAINT `Player_file_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `Users` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Time`
--
ALTER TABLE `Time`
  ADD CONSTRAINT `Time_ibfk_1` FOREIGN KEY (`idChat`) REFERENCES `Chat` (`idChat`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
