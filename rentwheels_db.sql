-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-07-2026 a las 06:30:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rentwheels_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `turista_id` int(11) NOT NULL,
  `vehiculo_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `monto_total` decimal(10,2) DEFAULT NULL,
  `recargo_aplicado` decimal(10,2) DEFAULT 0.00,
  `recargo_porcentaje` int(11) DEFAULT 0,
  `estado` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  `pagada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_reserva` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pagado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `turista_id` int(11) NOT NULL,
  `asunto` varchar(255) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `fecha_respuesta` timestamp NULL DEFAULT NULL,
  `estado` enum('abierto','respondido','cerrado') DEFAULT 'abierto',
  `cerrado_por` int(11) DEFAULT NULL,
  `fecha_cerrado` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('turista','compania','administrador','soporte') NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `cuenta_bancaria` varchar(50) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `nombre_banco` varchar(100) DEFAULT NULL,
  `qr_url` varchar(255) DEFAULT NULL,
  `ultima_conexion` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `saldo_pendiente` decimal(10,2) NOT NULL DEFAULT 0.00,
  `baneado` tinyint(1) NOT NULL DEFAULT 0,
  `codigo_verificacion` varchar(10) DEFAULT NULL,
  `verificado` tinyint(1) NOT NULL DEFAULT 0,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol`, `ciudad`, `last_login`, `cuenta_bancaria`, `telefono_contacto`, `nombre_banco`, `qr_url`, `ultima_conexion`, `created_at`, `saldo_pendiente`, `baneado`, `codigo_verificacion`, `verificado`, `reset_token`, `reset_token_expiry`) VALUES
(29, 'SON', 'SON@gmail.com', '$2y$10$5PxSF5DE2PT8lCIWrklCou4xpMmWGMhP8MoAIZCHQzFd5w1cC5mqC', 'compania', 'Ciudad de Panamá', '2026-07-08 23:28:33', '1111111', '6666-6666', 'Bac', 'uploads/qr_6a4eb08a40b94.jpeg', NULL, '2026-07-08 20:18:18', 0.00, 0, NULL, 1, NULL, NULL),
(31, 'Yandel Sanchez', 'xo@gmail.com', '$2y$10$vLlwHI4fBuAsYlC.Db.8hOBdU/PTCsJl5LQ643EFDOEcFoOIzxsky', 'turista', '', '2026-07-08 22:55:24', '', '', '', '', NULL, '2026-07-08 22:11:06', 0.00, 0, NULL, 0, NULL, NULL),
(39, 'Admin', 'admin@rentwheels.com', '$2y$10$rqTcEtXu5nc0rWsw16onHOGpiVp2rg8PW.96HVB9xjK87mDqh89A2', 'administrador', NULL, '2026-07-08 22:55:44', NULL, NULL, NULL, NULL, NULL, '2026-07-08 22:57:06', 0.00, 0, NULL, 0, NULL, NULL),
(42, 'Bahía motors', 'su@gmail.com', '$2y$10$OL5k2pyWQrBMco/4YoFiqeN.IeM4/gRKB18X5ZQTsox3JtWUEiUvC', 'compania', 'Colón', '2026-07-08 22:13:43', '0099659874', '368-25185', 'Banitsmo', 'uploads/qr_6a4f11dc4f25c.jpeg', NULL, '2026-07-09 03:13:32', 0.00, 0, '821716', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `compania_id` int(11) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `precio_por_dia` decimal(10,2) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `imagen_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id`, `compania_id`, `marca`, `modelo`, `anio`, `precio_por_dia`, `disponible`, `imagen_url`) VALUES
(32, 29, 'Acura', 'NSX', 2026, 90.00, 1, 'https://cdn.motor1.com/images/mgl/16A0M/s3/2017-acura-nsx-review.jpg'),
(33, 29, 'Acura', 'ADX', 2025, 80.00, 1, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSMpumJ56gqJl96EY28eV-QstgFdtPH_jDIRpIcfjQrKQ&s=10'),
(34, 29, 'Honda', 'CR-V', 2026, 70.00, 1, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcShm6WnYOZ6iVpLdlu4yc5ZxQHmcD7eAcKinFNoMGLWyw&s=10'),
(36, 29, 'Honda', 'HR-V', 2026, 80.00, 1, 'https://d31sro4iz4ob5n.cloudfront.net/upload/car/hr-v-2025/color/lhd-platinum-white-pearl/1.png?v=952197729');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turista_id` (`turista_id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turista_id` (`turista_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compania_id` (`compania_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`turista_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`);

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`turista_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`compania_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
