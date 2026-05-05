drop database if exists chat;
CREATE database chat;
use chat;

CREATE TABLE `Chats` (
  `idChat` int(10) UNSIGNED NOT NULL,
  `chatName` varchar(100) NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Messages` (
  `idMessage` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL,
  `idChat` int(10) UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Users` (
  `idUser` int(10) UNSIGNED NOT NULL,
  `Email` varchar(100) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `Chats`
  ADD PRIMARY KEY (`idChat`),
  ADD KEY `idUser` (`idUser`);

ALTER TABLE `Messages`
  ADD PRIMARY KEY (`idMessage`),
  ADD KEY `idChat` (`idChat`),
  ADD KEY `Messages_ibfk_1` (`idUser`);

ALTER TABLE `Users`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `Email` (`Email`);

ALTER TABLE `Chats`
  MODIFY `idChat` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `Messages`
  MODIFY `idMessage` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

ALTER TABLE `Users`
  MODIFY `idUser` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `Chats`
  ADD CONSTRAINT `Chats_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `Users` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `Messages`
  ADD CONSTRAINT `Messages_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `Users` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Messages_ibfk_2` FOREIGN KEY (`idChat`) REFERENCES `Chats` (`idChat`) ON DELETE CASCADE ON UPDATE CASCADE;
