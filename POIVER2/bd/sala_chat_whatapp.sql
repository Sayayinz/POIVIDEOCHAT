-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-03-2022 a las 00:14:45
-- Versión del servidor: 10.4.22-MariaDB
-- Versión de PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sala_chat_whatapp`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clickuser`
--

CREATE TABLE `clickuser` (
  `id` int(10) NOT NULL,
  `UserIdSession` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  `clickUser` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `clickuser`
--

INSERT INTO `clickuser` (`id`, `UserIdSession`, `clickUser`) VALUES
(8, '9', '12'),
(9, '12', '9');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `msjs`
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `imagen` varchar(50) DEFAULT NULL,
  `estatus` varchar(10) DEFAULT NULL,
  `fecha_registro` varchar(50) DEFAULT NULL,
  `email_user` varchar(250) DEFAULT NULL,
  `password` varchar(250) DEFAULT NULL,
  `nombre_apellido` varchar(250) DEFAULT NULL,
  `fecha_session` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `imagen`, `estatus`, `fecha_registro`, `email_user`, `password`, `nombre_apellido`, `fecha_session`) VALUES
(7, '80d2e60ded.png', 'Activo', '14/05/2019', 'admin@gmail.com', '123', 'Urian V.', '09/06/2020 09:28 pm'),
(9, '4802244def.png', 'Activo', '15/05/2019', 'any@gmail.com', '123', 'Any Somosa', '20/03/2022 12:48 pm'),
(12, '7209654fc0.png', 'Activo', '20/03/2022 11:4', 'brenda@gmail.com', '123', 'Brenda Viera', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clickuser`
--
ALTER TABLE `clickuser`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `msjs`
--
ALTER TABLE `msjs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clickuser`
--
ALTER TABLE `clickuser`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `msjs`
--
ALTER TABLE `msjs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE `grupos` (
  `id_grupo` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre_grupo` VARCHAR(255) NOT NULL,
  `id_creador` INT NOT NULL,
  `imagen_grupo` VARCHAR(255) DEFAULT NULL, -- Opcional: para ícono de grupo
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_creador`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE `miembros_grupo` (
  `id_relacion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_grupo` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `fecha_union` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_grupo`) REFERENCES `grupos`(`id_grupo`) ON DELETE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `grupo_usuario_unique` (`id_grupo`, `id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `msjs`
  ADD COLUMN `id_grupo_receptor` INT DEFAULT NULL AFTER `to_id`,
  ADD CONSTRAINT `fk_msjs_grupo` FOREIGN KEY (`id_grupo_receptor`) REFERENCES `grupos`(`id_grupo`) ON DELETE SET NULL;



ALTER TABLE `clickuser`
ADD COLUMN `tipoClick` ENUM('user', 'group') DEFAULT 'user' AFTER `clickUser`;
  

  CREATE TABLE `recompensas_niveles` (
  `id_nivel` INT PRIMARY KEY,
  `nombre_nivel` VARCHAR(50),
  `puntos_para_alcanzar` INT,
  `color_contacto_fondo` VARCHAR(7), -- Ejemplo: #FFD700
  `clase_css_marco_perfil` VARCHAR(50) -- Ejemplo: 'marco-nivel-oro'
);

-- Ejemplo de datos iniciales
INSERT INTO `recompensas_niveles` (id_nivel, nombre_nivel, puntos_para_alcanzar, color_contacto_fondo, clase_css_marco_perfil) VALUES
(1, 'Novato', 0, '#FFFFFF', 'marco-nivel-base'),
(2, 'Aprendiz', 100, '#E0E0E0', 'marco-nivel-bronce'),
(3, 'Veterano', 250, '#C0C0C0', 'marco-nivel-plata'),
(4, 'Maestro', 500, '#FFD700', 'marco-nivel-oro'),
(5, 'Leyenda', 1000, '#E67E22', 'marco-nivel-leyenda');

CREATE TABLE `tareas_grupo` (
  `id_tarea` INT AUTO_INCREMENT PRIMARY KEY,
  `id_grupo` INT NOT NULL,
  `id_creador` INT NOT NULL,
  `titulo_tarea` VARCHAR(255) NOT NULL,
  `descripcion_tarea` TEXT DEFAULT NULL,
  `fecha_creacion_tarea` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `completada_general` BOOLEAN DEFAULT FALSE, -- Indica si todos los miembros la completaron
  FOREIGN KEY (`id_grupo`) REFERENCES `grupos`(`id_grupo`) ON DELETE CASCADE,
  FOREIGN KEY (`id_creador`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `clickuser` ADD UNIQUE KEY `unique_UserIdSession` (`UserIdSession`);