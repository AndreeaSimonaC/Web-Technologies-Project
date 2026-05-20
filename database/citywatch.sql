-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 03:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `citywatch`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Plastic'),
(2, 'Paper'),
(3, 'Glass'),
(4, 'Metal'),
(5, 'Household');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','progress','resolved','archived') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `category_id`, `location`, `description`, `status`, `created_at`, `latitude`, `longitude`, `worker_id`, `resolved_at`) VALUES
(4, 2, 5, 'Banca Națională a României – Sucursala Iași, 17, Bulevardul Ștefan cel Mare și Sfânt, Centru, Iași, Iași Metropolitan Area, Iași, 700067, Romania', 'TESTTT', 'resolved', '2026-04-29 11:31:10', 47.1605695210656, 27.584609985351566, 6, NULL),
(5, 3, 4, 'DN28, Iași, Iași Metropolitan Area, Iași, 700410, Romania', 'Oh no gunoi', 'archived', '2026-04-29 13:14:37', 47.16862262391973, 27.51310157511827, 6, '2026-05-13 14:56:25'),
(6, 3, 4, 'DN28, Iași, Iași Metropolitan Area, Iași, 700410, Romania', 'Oh no gunoi', 'archived', '2026-04-29 13:16:42', 47.16862262391973, 27.51310157511827, 6, NULL),
(7, 3, 3, 'Facultatea de construcții și instalații, 1, Bulevardul Profesor Dimitrie Mangeron, Podu Roș, Podul Roș, Iași, Iași Metropolitan Area, Iași, 700050, Romania', 'Paper Test', 'progress', '2026-05-13 16:06:45', 47.15193150330104, 27.589073181152347, 1, NULL),
(8, 3, 3, 'Z5, Șoseaua Arcu, Gară, Iași, Iași Metropolitan Area, Iași, 700135, Romania', 'Glass test', 'pending', '2026-05-13 16:06:59', 47.1711900232274, 27.56555557250977, NULL, NULL),
(9, 3, 1, 'Bloc F4, 107, Strada Ciurchi, Tătărași Sud, Iași, Iași Metropolitan Area, Iași, 700389, Romania', 'Plastic test', 'pending', '2026-05-13 16:07:15', 47.15578376577611, 27.615337371826175, NULL, NULL),
(10, 17, 1, 'Stradela Sofia Nădejde, Galata, Iași, Iași Metropolitan Area, Iași, 700620, Romania', 'More gunoi', 'archived', '2026-05-20 11:19:27', 47.15555030327228, 27.564353942871094, 12, '2026-05-20 11:27:39'),
(11, 17, 2, 'Strada Avionului, Aviației, Iași, Iași Metropolitan Area, Iași, 700930, Romania', 'Even more gunoi', 'progress', '2026-05-20 11:19:40', 47.156133957608596, 27.632331848144535, 12, NULL),
(12, 17, 4, 'C.F.S. 2, Bulevardul Metalurgiei, Zona Industrială, Iași, Iași Metropolitan Area, Iași, 700393, Romania', 'So much gunoi', 'pending', '2026-05-20 11:19:55', 47.14819570936703, 27.62048721313477, NULL, NULL),
(13, 17, 5, 'Bloc 908A, 4-6, Strada Carpați, Nicolina, Baza 3, Galata, Valea Adâncă, Iași, Iași Metropolitan Area, Iași, 700729, Romania', 'Who did this?', 'pending', '2026-05-20 11:20:08', 47.143992627319655, 27.582378387451175, NULL, NULL),
(14, 17, 3, 'Școala Junior, Strada Păcurari, Centru, Iași, Iași Metropolitan Area, Iași, 700128, Romania', 'wow', 'archived', '2026-05-20 11:20:25', 47.169439537161296, 27.575340270996094, 12, '2026-05-20 11:27:46'),
(15, 17, 4, 'Dekor, 4, Bulevardul Tudor Vladimirescu, Moara de Vânt, Iași, Iași Metropolitan Area, Iași, 700303, Romania', 'whatt', 'progress', '2026-05-20 11:20:45', 47.16640522464319, 27.5947380065918, 12, NULL),
(16, 17, 1, 'Șoseaua Bucium, Baza 3, Bucium, Iași, Iași Metropolitan Area, Iași, 700298, Romania', 'trashh', 'pending', '2026-05-20 11:21:06', 47.12449064997278, 27.61997222900391, NULL, NULL),
(17, 17, 4, '1 Corp B, Strada Atelierului, Baza 3, Bularga, Iași, Iași Metropolitan Area, Iași, 700154, Romania', 'household', 'progress', '2026-05-20 11:26:55', 47.147144970011674, 27.60452270507813, 1, NULL),
(18, 11, 2, 'Copou', 'Garbage not collected', 'resolved', '2026-05-30 16:00:00', 47.1753, 27.5749, NULL, NULL),
(19, 2, 5, 'Tudor Vladimirescu', 'Broken glass on street', 'pending', '2026-05-10 15:00:00', 47.1585, 27.6014, NULL, NULL),
(20, 2, 2, 'Podu Ros', 'Metal waste dumped', 'progress', '2026-05-06 11:00:00', 47.158, 27.583, NULL, NULL),
(22, 29, 3, 'Copou', 'Garbage not collected', 'pending', '2026-05-09 09:00:00', 47.1753, 27.5749, NULL, NULL),
(23, 22, 5, 'Tudor Vladimirescu', 'Broken glass on street', 'progress', '2026-05-14 19:00:00', 47.1585, 27.6014, NULL, NULL),
(24, 27, 5, 'Podu Ros', 'Metal waste dumped', 'resolved', '2026-05-08 13:00:00', 47.158, 27.583, NULL, NULL),
(25, 2, 3, 'Alexandru cel Bun', 'Illegal dumping of waste', 'pending', '2026-05-02 10:00:00', 47.1682, 27.55, NULL, NULL),
(26, 17, 5, 'Nicolina', 'Overflowing trash bin household waste', 'pending', '2026-05-01 20:00:00', 47.1505, 27.57, NULL, NULL),
(27, 19, 3, 'Dacia', 'Household waste left on sidewalk', 'progress', '2026-05-26 20:00:00', 47.18, 27.54, NULL, NULL),
(28, 25, 4, 'Pacurari', 'Metal waste left behind', 'pending', '2026-05-09 18:00:00', 47.175, 27.56, NULL, NULL),
(29, 24, 2, 'CUG', 'Household waste dumped', 'pending', '2026-05-19 15:00:00', 47.14, 27.58, NULL, NULL),
(30, 29, 4, 'Bularga', 'Big garbage pile', 'pending', '2026-05-31 13:00:00', 47.15, 27.61, NULL, NULL),
(31, 23, 2, 'Cantemir', 'Plastic waste scattered', 'resolved', '2026-05-20 12:00:00', 47.155, 27.59, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `status`) VALUES
(1, 'test1', 'test1@t.com', '$2y$10$nIu.7Cut0swz2ubkriBnoO6qdRh0tRcc5p2ENV2kgrjz5EgC4G5ZW', 'worker', '2026-04-26 10:56:31', 'inactive'),
(2, 'test2', 'test2@t.com', '$2y$10$86JWU5XmqreiX0V1cEPRre/qwE6AyWTsd5VwLBM1CZLKHuEHObmpK', 'user', '2026-04-28 12:40:34', 'inactive'),
(3, 'test', 'test@t.com', '$2y$10$1C9KksY5I6885TELDUxCLOsUPdQTN20auwKzD7lsH6TfrbIAMiFX6', 'user', '2026-04-29 09:49:54', 'inactive'),
(8, 'admin2', 'admin2@citywatch.com', '$2y$10$5eQIzt36T6Rcx6OoVKcGQuAicv.D323GkFn00hfPhy0ZyKcfSv/ty', 'admin', '2026-05-14 12:05:32', 'active'),
(9, 'admin3', 'admin3@citywatch.com', '$2y$10$x4oA0yDUvicDk6lMjg5cm.YGlNYZFZw6DF6OZK8m/3SY9hb0f3MuC', 'admin', '2026-05-14 12:05:32', 'active'),
(10, 'admin4', 'admin4@citywatch.com', '$2y$10$TnNULv43qWoenx6DWaviXOLbluiaBF8byQH7voT9PBmyNHD9HaanK', 'admin', '2026-05-14 12:05:32', 'active'),
(11, 'admin5', 'admin5@citywatch.com', '$2y$10$Ht6jYGGnTzPJ.nOftXeE0.pVVBTnJxsEYEV/WsKodJwmSlhESbbXO', 'admin', '2026-05-14 12:05:32', 'active'),
(12, 'worker1', 'worker1@citywatch.com', '$2y$10$sdMk0VdwGT2ff87i/0T3yOYKZM4F8geJGYZDQz4l7elktZVYcVdn.', 'worker', '2026-05-14 12:05:32', 'active'),
(13, 'worker2', 'worker2@citywatch.com', '$2y$10$0WSBxl5t.Yfh3CKbSQTwOOdYrx.hNDyMooRl8Z6kFWzl/jGxL1h5e', 'worker', '2026-05-14 12:05:32', 'active'),
(14, 'worker3', 'worker3@citywatch.com', '$2y$10$XG6WCNNMYPSY2o/NIZgfeu64XUFoou6CT3JenWb/eRgvxe1/Etg5a', 'worker', '2026-05-14 12:05:32', 'active'),
(15, 'worker4', 'worker4@citywatch.com', '$2y$10$D5fY0XXL13kSCLU0uQjxnO/I5iJxzCN/r9CDS8nPE2/s.ra4mA/JS', 'worker', '2026-05-14 12:05:32', 'active'),
(16, 'worker5', 'worker5@citywatch.com', '$2y$10$nNoGISX8TXItLTiUnNx9vOY/zRuU5kWWsnK2TKEHpJas4.VjzzNwK', 'worker', '2026-05-14 12:05:32', 'active'),
(17, 'user1', 'user1@citywatch.com', '$2y$10$xXs7jFmLI/uf9czEN9zHiON5Le2SjynxI2tzAgCBBU9MokX8uf4oq', 'user', '2026-05-14 12:05:32', 'inactive'),
(18, 'user2', 'user2@citywatch.com', '$2y$10$KidFSBLkiNGk/.i6lirGW.mHs6N40lgs3F44DGpMmpvupuclOsLPG', 'user', '2026-05-14 12:05:32', 'inactive'),
(19, 'user3', 'user3@citywatch.com', '$2y$10$kAq3buKquLVBOBL14cfXeu.T7CNNJ0RikITRng9IQ08KL9n.kf/3S', 'user', '2026-05-14 12:05:32', 'inactive'),
(22, 'user4', 'user4@citywatch.com', '$2y$10$qNSEG6WeoRxd4cypDXI97.gwvBQIpKMZ11bypvBiMd/shAKawJfpq', 'user', '2026-05-20 13:04:34', 'active'),
(23, 'user5', 'user5@citywatch.com', '$2y$10$uAJ/vZCAp/GFPvMN54JKpeUKtDGvcyG.dtcBiSCMnuK1XxbiPwk0i', 'user', '2026-05-20 13:04:34', 'active'),
(24, 'user6', 'user6@citywatch.com', '$2y$10$HQMKvzHjhiD.Z.Xwr/KC6.UUMbXV5KouVBysawDG49MF/FR7NQUuy', 'user', '2026-05-20 13:04:34', 'active'),
(25, 'user7', 'user7@citywatch.com', '$2y$10$K6GRl93YV3yZw86gvixutOulQsqIcukJYsC9adVx6Atp/PkkJl5gK', 'user', '2026-05-20 13:04:34', 'active'),
(26, 'user8', 'user8@citywatch.com', '$2y$10$N0TXiS377sZDMeFOr3bIuOMR3OZDPSiWbl3tZFeUoZtkFgLqgpHfK', 'user', '2026-05-20 13:04:34', 'active'),
(27, 'user9', 'user9@citywatch.com', '$2y$10$citofbgjpxZSaQSG7uKPLuzJE9.YGqs3EFssottQME1Ap.0H/9rxO', 'user', '2026-05-20 13:04:34', 'active'),
(28, 'user10', 'user10@citywatch.com', '$2y$10$VZd5ZLHOm1LTX7XD5zZAbe1BMjCN6cpqeMGXU1Wm48CNdCGjHeVoC', 'user', '2026-05-20 13:04:35', 'active'),
(29, 'user11', 'user11@citywatch.com', '$2y$10$MK8WiefrpLRFLfJqjBTu2OjH0AhyBAQ50YMYKhw5AXEOhUgzfCGq6', 'user', '2026-05-20 13:04:35', 'active'),
(30, 'user12', 'user12@citywatch.com', '$2y$10$fqP3t8geRLuzN3wfyztAuOdFyzJIi2Z6uz6z.JOL.gA5buUR8Pz8S', 'user', '2026-05-20 13:04:35', 'active'),
(31, 'user13', 'user13@citywatch.com', '$2y$10$IwOvN2zWvl1dbNYHPl.xtuzlgvMS0ucD0TIzecHg6NtswGCGcWB8O', 'user', '2026-05-20 13:04:35', 'active'),
(32, 'worker6', 'worker6@citywatch.com', '$2y$10$/0Rh8xxeIJICxARNY.nTT.W7DDRDIL4XYWQAkz8/tR8q529IiYIau', 'worker', '2026-05-20 13:05:53', 'active'),
(33, 'worker7', 'worker7@citywatch.com', '$2y$10$qTN.gngZmXVO86OCtPPR5Ot.RzwBuFNOs7MZjZ0NbWj0j2krUyayi', 'worker', '2026-05-20 13:05:53', 'active'),
(34, 'worker8', 'worker8@citywatch.com', '$2y$10$baVIQKDe16cl/Z/5oxtE1.nONb.SutvkGZt.7M8FuZpeXRxS5J8S6', 'worker', '2026-05-20 13:05:53', 'active'),
(35, 'worker9', 'worker9@citywatch.com', '$2y$10$ywoF2YXWOiYyKG0ybU0jAOqIHehZdNZvVNy1l2ltVZkq2sy1UR6qq', 'worker', '2026-05-20 13:05:53', 'active'),
(36, 'worker10', 'worker10@citywatch.com', '$2y$10$c.KyFpUiYwrEulPI.kpiD.utUcNzKzyj1OmaeZBgLb4SB8n0.uCrS', 'worker', '2026-05-20 13:05:53', 'active'),
(37, 'worker11', 'worker11@citywatch.com', '$2y$10$jQb5EwGit4e3wIvfmKiFwOz8qbvG4yIBEXim25dHVA6k3Q69GedP.', 'worker', '2026-05-20 13:05:53', 'active'),
(38, 'worker12', 'worker12@citywatch.com', '$2y$10$BSONqtDPPK2bTiPzAaKQ7u255jgBt2me1S3OFW8cE5jwGxflLtFHW', 'worker', '2026-05-20 13:05:53', 'active'),
(39, 'worker13', 'worker13@citywatch.com', '$2y$10$jvCjwzqA/LamOdZVl2EDcu4EXd3YjoRa.nzesgMpNfAOGD7zJRR/m', 'worker', '2026-05-20 13:05:53', 'active'),
(40, 'worker14', 'worker14@citywatch.com', '$2y$10$HCiNNgcHnFSuvn7hG3f/5.I3vKzHHc7K/pNVkSYGyEtaHalShZNte', 'worker', '2026-05-20 13:05:54', 'active'),
(41, 'worker15', 'worker15@citywatch.com', '$2y$10$wiFbzQ9hLUWCgiRIPbR/DOUIR4AsIMLzk9T3a3MPyL8hrh6wiKmeC', 'worker', '2026-05-20 13:05:54', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
