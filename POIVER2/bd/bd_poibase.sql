-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2025 at 07:48 AM
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
-- Database: `bd_chat_whatsapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `clickuser`
--

CREATE TABLE `clickuser` (
  `id` int(10) NOT NULL,
  `UserIdSession` varchar(50) DEFAULT NULL,
  `clickUser` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `clickuser`
--

INSERT INTO `clickuser` (`id`, `UserIdSession`, `clickUser`) VALUES
(1, '11', '13'),
(2, '12', '11'),
(3, '13', '10');

-- --------------------------------------------------------

--
-- Table structure for table `msjs`
--

CREATE TABLE `msjs` (
  `id` int(11) NOT NULL,
  `user` varchar(250) DEFAULT NULL,
  `user_id` int(250) DEFAULT NULL,
  `to_user` varchar(250) DEFAULT NULL,
  `to_id` int(250) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `fecha` varchar(250) DEFAULT NULL,
  `nombre_equipo_user` varchar(250) DEFAULT NULL,
  `leido` varchar(100) DEFAULT NULL,
  `sonido` varchar(10) DEFAULT NULL,
  `archivos` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `msjs`
--

INSERT INTO `msjs` (`id`, `user`, `user_id`, `to_user`, `to_id`, `message`, `fecha`, `nombre_equipo_user`, `leido`, `sonido`, `archivos`) VALUES
(1, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, 'hola como estas', '26/03/2025 09:43 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(2, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, NULL, '26/03/2025 09:43 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, 'ecc56eebfc.png'),
(3, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, 'que onda', '29/03/2025 08:03 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(4, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, 'hola', '31/03/2025 07:29 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(5, 'carlos@hotmail.com', 12, 'Carlos2 ', 11, 'si', '31/03/2025 07:29 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(6, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, 'esta chido', '31/03/2025 07:59 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(7, 'carlos@hotmail.com', 12, 'Carlos2 ', 11, 'mas chido la nueva switch', '31/03/2025 08:07 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(8, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, 'holaaaaaa', '31/03/2025 08:25 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(9, 'carlos@hotmail.com', 12, 'Carlos2 ', 11, 'kkkk', '31/03/2025 08:25 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(10, 'carlos@hotmail.com', 12, 'Carlos2 ', 11, 'kkkk', '31/03/2025 08:25 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(11, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, 'hey ayer hice un nuevo video, puedes checarlo', '31/03/2025 08:27 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(12, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, NULL, '16/05/2025 06:38 pm', 'DESKTOP-5VVCKPU', 'NO', NULL, 'd8cde70656.png'),
(13, 'carlos_abr16@hotmail.com', 11, 'Carlos3 ', 12, 'pikmin', '16/05/2025 06:38 pm', 'DESKTOP-5VVCKPU', 'NO', NULL, NULL),
(14, 'carlos_abr16@hotmail.com', 11, 'carOnline ', 13, 'hola jooakin', '16/05/2025 07:16 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(15, 'carlos_abr16@hotmail.com', 11, 'carOnline ', 13, NULL, '16/05/2025 07:17 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, '03d9e267f0.jpg'),
(16, 'carlos_abr16@hotmail.com', 11, 'carOnline ', 13, 'mra l juego nuevo', '16/05/2025 07:17 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, NULL),
(17, 'carlos_abr16@hotmail.com', 11, 'carOnline ', 13, NULL, '16/05/2025 07:18 pm', 'DESKTOP-5VVCKPU', 'SI', NULL, 'c22d07f072.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nombre_apellido` varchar(250) DEFAULT NULL,
  `email_user` varchar(250) DEFAULT NULL,
  `password` varchar(250) DEFAULT NULL,
  `imagen` varchar(50) DEFAULT NULL,
  `estatus` varchar(10) DEFAULT NULL,
  `fecha_registro` varchar(50) DEFAULT NULL,
  `fecha_session` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nombre_apellido`, `email_user`, `password`, `imagen`, `estatus`, `fecha_registro`, `fecha_session`) VALUES
(1, 'Urian', 'dev@gmail.com', '$2y$10$ysOZo2KGH4w/7CnHfnyf1OrlN7JkzMEv7JzFQCsh9ZlksOEvDYuv6', '6593d72014.jpeg', 'Inactiva', '09/09/2023 02:32 pm', '09/09/2023 02:51 pm'),
(2, 'Brenda', 'brenda@gmail.com', '$2y$10$KaytI.EMiwTaOE9pDTVMSeVy7foOKWsQaPBD8r3RU4OY2zml/SyR.', 'f0b0045e42.jpg', 'Inactiva', '09/09/2023 02:52 pm', '09/09/2023 02:53 pm'),
(3, 'Abelardo Perez', 'abelardo@gmail.com', '$2y$10$qil5sHQ8aRAgIxLH54ETUukHTHuWmJwSobFe4hoP6k4URyjEIrOG.', '6f842c4fe3.jpeg', 'Inactiva', '09/09/2023 02:53 pm', '09/09/2023 02:54 pm'),
(4, 'Cristian R.', 'cristian@hotmail.com', '$2y$10$xDZn40SPhfMagbYTsz4MZ.1L7XD.VN5OIcJCZzjrWWnvE5HjWtOci', '45d9649ddf.png', 'Activo', '09/09/2023 02:54 pm', NULL),
(6, 'Franco E.', 'franco@gmail.com', '$2y$10$5VLSB3NqFVjCOE.I8ooEY.kV9S1c96zDWDweaXH7RdG15v2p/RAIC', '17d760c7b0.jpeg', 'Activo', '09/09/2023 02:57 pm', NULL),
(8, 'Deyna Castellano', 'deyna@gmail.com', '$2y$10$iHn2vpc.qc.eYPcE2aWsiOVm9gAyek4NeVZr/Qfvoo31e7JQsNF9S', 'c02d4a11e0.jpg', 'Activo', '09/09/2023 03:00 pm', NULL),
(9, 'Urian V.', 'urian@gmail.com', '$2y$10$Jw1IU3IGpSpMUHmr7jcdUOjc8OH0Mte0SpBUsfgGtm7GP7t2DEMze', '529a73c510.png', 'Activo', '09/09/2023 03:01 pm', NULL),
(10, 'Carlos', '', '$2y$10$X1Xq0yIYAu8CXU25nBCKHOMuSpxvn0oca.ubiXtksZALoPQYtMH6u', '9bb3e336be.jpg', 'Activo', '26/03/2025 09:37 pm', NULL),
(11, 'Carlos2', 'carlos_abr16@hotmail.com', '$2y$10$vwDfzv1EsXI7jxPJag95leYzVDh2fehjXM3MLO4ONPxrdZ1/Oouhm', '19e738a152.jpg', 'Inactiva', '26/03/2025 09:39 pm', '16/05/2025 07:34 pm'),
(12, 'Carlos3', 'carlos@hotmail.com', '$2y$10$I7AZczrBu7RGCWlWVU0Ly.VYdNS6GvsgOFX4JGxRwpVJO4oZ9ncgm', '13d08ed664.jpg', 'Inactiva', '26/03/2025 09:42 pm', '31/03/2025 08:29 pm'),
(13, 'carOnline', 'online@hotmail.com', '$2y$10$AE.I7VTpD8xu2pneMEUz9.H1CsV7wyxg83vwTN1W0vrcngDXr6vVa', 'fb15691ec3.png', 'Activo', '16/05/2025 07:14 pm', '16/05/2025 07:23 pm');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clickuser`
--
ALTER TABLE `clickuser`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `msjs`
--
ALTER TABLE `msjs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clickuser`
--
ALTER TABLE `clickuser`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `msjs`
--
ALTER TABLE `msjs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
