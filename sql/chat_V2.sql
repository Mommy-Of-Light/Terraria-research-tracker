-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 03 fév. 2026 à 07:46
-- Version du serveur : 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Version de PHP : 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

drop database if exists chat_V2;
CREATE database chat_V2;
use chat_V2;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `chat_V2`
--

-- --------------------------------------------------------

--
-- Structure de la table `Chat`
--

CREATE TABLE `Chat` (
  `idChat` int(11) UNSIGNED NOT NULL,
  `idCreator` int(11) UNSIGNED NOT NULL,
  `ChatName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Structure de la table `Time`
--

CREATE TABLE `Time` (
  `idTime` int(11) UNSIGNED NOT NULL,
  `idChat` int(11) UNSIGNED NOT NULL,
  `TimeStamp` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE `Users` (
  `idUser` int(11) UNSIGNED NOT NULL,
  `Email` varchar(100) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `Password` varchar(256) NOT NULL,
  `pfp` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `idChat` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Message`
--
ALTER TABLE `Message`
  MODIFY `idMessage` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Time`
--
ALTER TABLE `Time`
  MODIFY `idTime` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Users`
--
ALTER TABLE `Users`
  MODIFY `idUser` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Contraintes pour la table `Time`
--
ALTER TABLE `Time`
  ADD CONSTRAINT `Time_ibfk_1` FOREIGN KEY (`idChat`) REFERENCES `Chat` (`idChat`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
