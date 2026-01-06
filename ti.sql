-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-01-2026 a las 22:29:37
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
-- Base de datos: `ti`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID del registro',
  `nombre_archivo` varchar(255) NOT NULL COMMENT 'Nombre original del archivo',
  `tipo_mime` varchar(100) NOT NULL COMMENT 'Tipo MIME (image/png, application/pdf, etc)',
  `tamano_bytes` bigint(20) UNSIGNED NOT NULL COMMENT 'Tamaño del archivo en bytes',
  `num_version` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Número de versión del archivo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de carga'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `nombre_archivo`, `tipo_mime`, `tamano_bytes`, `num_version`, `fecha_registro`) VALUES
(4, 'Balanced Scorecard.pdf', 'application/pdf', 290969, 1, '2026-01-04 05:08:52'),
(5, 'casoDeNegocio (1).pdf', 'application/pdf', 388154, 1, '2026-01-04 05:10:36'),
(6, 'CEDULA DE SERVICIO .docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 509903, 1, '2026-01-04 05:11:01'),
(7, 'Plan de Continuidad del Negocio.pdf', 'application/pdf', 119099, 1, '2026-01-04 05:11:36'),
(8, 'Metas de IT (COBIT).pdf', 'application/pdf', 36820, 1, '2026-01-04 05:11:43'),
(9, 'AnalisisDeRiesgos.pdf', 'application/pdf', 994141, 1, '2026-01-04 19:13:18'),
(11, 'Inventario de Activos.pdf', 'application/pdf', 279466, 1, '2026-01-04 19:18:48'),
(12, 'BIA_Operacional.pdf', 'application/pdf', 312673, 1, '2026-01-05 04:37:39'),
(13, 'BIA_Tactico_Proceso1.pdf', 'application/pdf', 344915, 1, '2026-01-05 04:37:44'),
(14, 'BIA_Tactico_Proceso2.pdf', 'application/pdf', 323328, 1, '2026-01-05 04:37:48'),
(15, 'BIA_Tactico_Proceso3.pdf', 'application/pdf', 346516, 1, '2026-01-05 04:37:53');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID del registro', AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
