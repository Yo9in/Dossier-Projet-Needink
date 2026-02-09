-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : lun. 09 fév. 2026 à 16:49
-- Version du serveur : 5.7.39
-- Version de PHP : 8.1.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `NeedInk`
--

-- --------------------------------------------------------

--
-- Structure de la table `appointment`
--

CREATE TABLE `appointment` (
  `id_appointment` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_artist` int(11) NOT NULL,
  `id_service` int(11) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `status` enum('PENDING','CONFIRMED','REFUSED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `appointment`
--

INSERT INTO `appointment` (`id_appointment`, `id_user`, `id_artist`, `id_service`, `start_at`, `end_at`, `status`, `created_at`) VALUES
(6, 5, 3, 3, '2026-02-18 09:00:00', '2026-02-18 13:00:00', 'PENDING', '2026-02-07 20:12:59'),
(7, 5, 3, 3, '2026-02-23 13:00:00', '2026-02-23 17:00:00', 'PENDING', '2026-02-07 20:13:52'),
(8, 5, 3, 4, '2026-02-09 14:00:00', '2026-02-09 15:00:00', 'PENDING', '2026-02-07 20:14:20'),
(9, 3, 4, 4, '2026-02-09 11:00:00', '2026-02-09 12:00:00', 'PENDING', '2026-02-07 20:15:45'),
(10, 3, 4, 1, '2026-02-12 14:00:00', '2026-02-12 15:00:00', 'PENDING', '2026-02-07 20:16:27'),
(11, 4, 1, 4, '2026-02-21 11:00:00', '2026-02-21 12:00:00', 'PENDING', '2026-02-07 20:18:33'),
(12, 4, 1, 2, '2026-02-23 13:00:00', '2026-02-23 15:00:00', 'PENDING', '2026-02-07 20:19:04'),
(13, 3, 2, 3, '2026-02-07 09:00:00', '2026-02-07 13:00:00', 'PENDING', '2026-02-08 01:27:09'),
(14, 3, 2, 3, '2026-02-18 09:00:00', '2026-02-18 13:00:00', 'PENDING', '2026-02-08 01:28:03');

-- --------------------------------------------------------

--
-- Structure de la table `artist`
--

CREATE TABLE `artist` (
  `id_artist` int(11) NOT NULL,
  `artist_name` varchar(150) NOT NULL,
  `bio` text,
  `speciality` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `artist`
--

INSERT INTO `artist` (`id_artist`, `artist_name`, `bio`, `speciality`, `is_active`) VALUES
(1, 'Alice', NULL, 'Ornemental', 1),
(2, 'Benoît', NULL, 'Watercolor', 1),
(3, 'Clara', NULL, 'Réaliste', 1),
(4, 'David', NULL, 'Japonais', 1);

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

CREATE TABLE `service` (
  `id_service` int(11) NOT NULL,
  `service_name` varchar(150) NOT NULL,
  `duration_min` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `service`
--

INSERT INTO `service` (`id_service`, `service_name`, `duration_min`, `is_active`) VALUES
(1, 'Tattoo Small/Flash (1h)', 60, 1),
(2, 'Tattoo Medium Size (2h)', 120, 1),
(3, 'Tattoo Big Piece (4h)', 240, 1),
(4, 'Consultation (1h)', 60, 1);

-- --------------------------------------------------------

--
-- Structure de la table `unavailability`
--

CREATE TABLE `unavailability` (
  `id_unavailability` int(11) NOT NULL,
  `id_artist` int(11) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `unavailability`
--

INSERT INTO `unavailability` (`id_unavailability`, `id_artist`, `start_at`, `end_at`, `reason`) VALUES
(1, 1, '2026-02-06 00:00:00', '2026-02-07 00:00:00', 'Jour off'),
(2, 1, '2026-02-16 00:00:00', '2026-02-21 00:00:00', 'Congés'),
(3, 2, '2026-02-10 00:00:00', '2026-02-11 00:00:00', 'Jour off'),
(4, 2, '2026-02-23 00:00:00', '2026-02-26 00:00:00', 'Congés'),
(5, 3, '2026-02-12 00:00:00', '2026-02-13 00:00:00', 'Jour off'),
(6, 3, '2026-02-02 13:00:00', '2026-02-02 18:00:00', 'Après-midi réservé (gros projet)'),
(7, 4, '2026-02-03 00:00:00', '2026-02-04 00:00:00', 'Jour off'),
(8, 4, '2026-02-18 00:00:00', '2026-02-19 00:00:00', 'Formation');

-- --------------------------------------------------------

--
-- Structure de la table `user_account`
--

CREATE TABLE `user_account` (
  `id_user` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('CLIENT','ADMIN') NOT NULL DEFAULT 'CLIENT',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `user_account`
--

INSERT INTO `user_account` (`id_user`, `email`, `firstname`, `lastname`, `telephone`, `password_hash`, `role`, `created_at`) VALUES
(1, 'client@test.com', 'Jean', 'Dupont', NULL, '$2y$10$kWb/rqwa90Q2T08RoUYiW.tmqXkgXjcd9wKWD8G9kQEm/0L2F3uPu', 'CLIENT', '2026-02-05 18:07:15'),
(2, 'admin@needink.test', 'Admin', 'NeedInk', NULL, '$2y$10$PGz3bK/VtiFx1vy.GrhCk.VuSMGlJ2PS.reYhPYm/rat89vRHgfC6', 'ADMIN', '2026-02-05 21:48:31'),
(3, 'client1@needink.test', 'Lucas', 'Martin', '0612345678', '$2y$10$kWb/rqwa90Q2T08RoUYiW.tmqXkgXjcd9wKWD8G9kQEm/0L2F3uPu', 'CLIENT', '2026-02-07 19:42:54'),
(4, 'client2@needink.test', 'Emma', 'Durand', '0623456789', '$2y$10$kWb/rqwa90Q2T08RoUYiW.tmqXkgXjcd9wKWD8G9kQEm/0L2F3uPu', 'CLIENT', '2026-02-07 19:42:54'),
(5, 'client3@needink.test', 'Nathan', 'Bernard', '0634567890', '$2y$10$kWb/rqwa90Q2T08RoUYiW.tmqXkgXjcd9wKWD8G9kQEm/0L2F3uPu', 'CLIENT', '2026-02-07 19:42:54');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`id_appointment`),
  ADD KEY `fk_appointment_service` (`id_service`),
  ADD KEY `idx_appointment_artist_start` (`id_artist`,`start_at`),
  ADD KEY `idx_appointment_user_start` (`id_user`,`start_at`);

--
-- Index pour la table `artist`
--
ALTER TABLE `artist`
  ADD PRIMARY KEY (`id_artist`);

--
-- Index pour la table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id_service`);

--
-- Index pour la table `unavailability`
--
ALTER TABLE `unavailability`
  ADD PRIMARY KEY (`id_unavailability`),
  ADD KEY `idx_unavailability_artist_start` (`id_artist`,`start_at`);

--
-- Index pour la table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `id_appointment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `artist`
--
ALTER TABLE `artist`
  MODIFY `id_artist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `service`
--
ALTER TABLE `service`
  MODIFY `id_service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `unavailability`
--
ALTER TABLE `unavailability`
  MODIFY `id_unavailability` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `fk_appointment_artist` FOREIGN KEY (`id_artist`) REFERENCES `artist` (`id_artist`),
  ADD CONSTRAINT `fk_appointment_service` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`),
  ADD CONSTRAINT `fk_appointment_user` FOREIGN KEY (`id_user`) REFERENCES `user_account` (`id_user`);

--
-- Contraintes pour la table `unavailability`
--
ALTER TABLE `unavailability`
  ADD CONSTRAINT `fk_unavailability_artist` FOREIGN KEY (`id_artist`) REFERENCES `artist` (`id_artist`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
