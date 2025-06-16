-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 16 juin 2025 à 16:08
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
-- Base de données : `stubborn_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250416131939', '2025-04-16 15:20:26', 118),
('DoctrineMigrations\\Version20250417073749', '2025-04-17 09:38:33', 83),
('DoctrineMigrations\\Version20250417094547', '2025-04-17 11:46:31', 109),
('DoctrineMigrations\\Version20250417122343', '2025-04-17 14:24:09', 55),
('DoctrineMigrations\\Version20250418143627', '2025-04-18 16:39:18', 174),
('DoctrineMigrations\\Version20250422122838', '2025-04-22 14:29:11', 78),
('DoctrineMigrations\\Version20250425074803', '2025-04-25 09:48:15', 430),
('DoctrineMigrations\\Version20250505155838', '2025-05-06 09:44:26', 71),
('DoctrineMigrations\\Version20250506075914', '2025-05-06 10:31:00', 176);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(32) NOT NULL,
  `total` int(10) UNSIGNED NOT NULL,
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `order_item`
--

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` int(10) UNSIGNED NOT NULL,
  `subtotal` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image_filename` varchar(255) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `is_featured` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product`
--

INSERT INTO `product` (`id`, `name`, `image_filename`, `price`, `is_featured`) VALUES
(1, 'Blackbelt', '6807c1e1349d5.jpg', 2990, 1),
(5, 'Bluebelt', '6807f34a08073.jpg', 2990, 0),
(6, 'Street', '6807f37843888.jpg', 3450, 0),
(7, 'Pokeball', '6807f399e75b0.jpg', 4500, 1),
(8, 'PinkLady', '6807f3bf593e6.jpg', 2990, 0),
(9, 'Snow', '6807f3f104260.jpg', 3200, 0),
(10, 'Greyback', '6807f42e953b4.jpg', 2850, 0),
(11, 'BlueCloud', '6807f44ab564b.jpg', 4500, 0),
(12, 'BornInUSA', '6807f46deda43.jpg', 5990, 1),
(13, 'GreenSchool', '6807f48f01fac.jpg', 4220, 0);

-- --------------------------------------------------------

--
-- Structure de la table `product_variant`
--

CREATE TABLE `product_variant` (
  `id` int(11) NOT NULL,
  `relation_id` int(11) NOT NULL,
  `size` varchar(255) NOT NULL,
  `stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product_variant`
--

INSERT INTO `product_variant` (`id`, `relation_id`, `size`, `stock`) VALUES
(1, 1, 'M', 5),
(2, 1, 'XS', 3),
(3, 1, 'S', 5),
(4, 1, 'L', 8),
(5, 1, 'XL', 2),
(6, 5, 'XS', 5),
(7, 5, 'S', 8),
(8, 5, 'M', 5),
(9, 5, 'L', 9),
(10, 5, 'XL', 2),
(11, 6, 'XS', 2),
(12, 6, 'S', 6),
(13, 6, 'M', 5),
(14, 6, 'L', 7),
(15, 6, 'XL', 3),
(16, 7, 'XS', 8),
(17, 7, 'S', 8),
(18, 7, 'M', 8),
(19, 7, 'L', 8),
(20, 7, 'XL', 8),
(21, 8, 'XS', 3),
(22, 8, 'S', 3),
(23, 8, 'M', 3),
(24, 8, 'L', 3),
(25, 8, 'XL', 3),
(26, 9, 'XS', 4),
(27, 9, 'S', 4),
(28, 9, 'M', 4),
(29, 9, 'L', 4),
(30, 9, 'XL', 4),
(31, 10, 'XS', 5),
(32, 10, 'S', 5),
(33, 10, 'M', 5),
(34, 10, 'L', 5),
(35, 10, 'XL', 2),
(36, 11, 'XS', 2),
(37, 11, 'S', 3),
(38, 11, 'M', 4),
(39, 11, 'L', 5),
(40, 11, 'XL', 6),
(41, 12, 'XS', 9),
(42, 12, 'S', 9),
(43, 12, 'M', 9),
(44, 12, 'L', 9),
(45, 12, 'XL', 11),
(46, 13, 'XS', 6),
(47, 13, 'S', 6),
(48, 13, 'M', 6),
(49, 13, 'L', 6),
(50, 13, 'XL', 6);

-- --------------------------------------------------------

--
-- Structure de la table `reset_password_request`
--

CREATE TABLE `reset_password_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(20) NOT NULL,
  `hashed_token` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `delivery_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `is_verified`, `name`, `delivery_address`) VALUES
(1, 'zabou2001@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$RloNOuHs355iJG7fzH6HDe/9pzNDvsvWCcZ.da3w14FjkHy2I9ozC', 0, 'zabou', NULL),
(2, 'isabelle.raynaud@lewebpluschouette.fr', '[\"ROLE_ADMIN\"]', '$2y$13$ct1wmu.PPxDGlGXDlVcVB./ygI7L8T0d15Q.LAV8qL8VGcAgnPuwG', 0, 'Isabelle', '123 rue de la fin');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E52FFDEEA76ED395` (`user_id`);

--
-- Index pour la table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_52EA1F098D9F6D38` (`order_id`),
  ADD KEY `IDX_52EA1F093B69A9AF` (`variant_id`);

--
-- Index pour la table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `product_variant`
--
ALTER TABLE `product_variant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_209AA41D3256915B` (`relation_id`);

--
-- Index pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7CE748AA76ED395` (`user_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `product_variant`
--
ALTER TABLE `product_variant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `FK_E52FFDEEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `FK_52EA1F093B69A9AF` FOREIGN KEY (`variant_id`) REFERENCES `product_variant` (`id`),
  ADD CONSTRAINT `FK_52EA1F098D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Contraintes pour la table `product_variant`
--
ALTER TABLE `product_variant`
  ADD CONSTRAINT `FK_209AA41D3256915B` FOREIGN KEY (`relation_id`) REFERENCES `product` (`id`);

--
-- Contraintes pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
