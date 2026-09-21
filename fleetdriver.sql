-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-09-2026 a las 22:57:23
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
-- Base de datos: `fleetdriver`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `modulo` enum('TRANSPORTE','ALMACEN') NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centros_distribucion`
--

CREATE TABLE `centros_distribucion` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `tipo_documento` varchar(150) NOT NULL,
  `fecha_expedicion` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `archivo_tipo` enum('PDF','LINK') NOT NULL,
  `archivo_valor` text NOT NULL,
  `fecha_carga` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inspecciones`
--

CREATE TABLE `inspecciones` (
  `id` int(11) NOT NULL,
  `modulo` enum('TRANSPORTE','ALMACEN') NOT NULL,
  `tipo` enum('CONDUCTOR','VEHICULO','OPERARIO') NOT NULL,
  `identificador` varchar(50) NOT NULL,
  `semana` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros`
--

CREATE TABLE `registros` (
  `id` int(11) NOT NULL,
  `centro_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo_contratista` enum('UC','OL') NOT NULL,
  `tipo_registro` enum('CONDUCTOR','VEHICULO','OPERARIO') NOT NULL,
  `modulo` enum('TRANSPORTE','ALMACEN') DEFAULT 'TRANSPORTE',
  `cargo` varchar(100) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `cedula` varchar(50) DEFAULT NULL,
  `placa` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_ingreso` date DEFAULT NULL,
  `entidad_manejo` varchar(100) DEFAULT 'N/A',
  `entidad_sena` varchar(100) DEFAULT 'SENA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_documento`
--

CREATE TABLE `tipos_documento` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `aplica_a` enum('CONDUCTOR','VEHICULO','AMBOS') NOT NULL,
  `es_obligatorio` tinyint(1) DEFAULT 1,
  `condicion_cargos` enum('TODOS','SOLO','EXCEPTO') DEFAULT 'TODOS',
  `cargos_relacionados` text DEFAULT NULL,
  `modulo` enum('AMBOS','TRANSPORTE','ALMACEN') DEFAULT 'AMBOS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `centro_id` int(11) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `cedula` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('SUPERADMIN','ADMIN','EMPLEADO') NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos_reporte`
--

CREATE TABLE `videos_reporte` (
  `id` int(11) NOT NULL,
  `contratista` varchar(10) NOT NULL,
  `nombre_conductor` varchar(100) DEFAULT NULL,
  `placa` varchar(20) DEFAULT NULL,
  `tiene_novedad` tinyint(1) DEFAULT 0,
  `descripcion_novedad` text DEFAULT NULL,
  `ruta_video` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `centros_distribucion`
--
ALTER TABLE `centros_distribucion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vencimiento` (`fecha_vencimiento`),
  ADD KEY `idx_registro` (`registro_id`);

--
-- Indices de la tabla `inspecciones`
--
ALTER TABLE `inspecciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `modulo` (`modulo`,`tipo`,`identificador`,`semana`);

--
-- Indices de la tabla `registros`
--
ALTER TABLE `registros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contratista` (`tipo_contratista`),
  ADD KEY `idx_tipo` (`tipo_registro`),
  ADD KEY `fk_registros_centro` (`centro_id`),
  ADD KEY `fk_registros_usuario` (`usuario_id`);

--
-- Indices de la tabla `tipos_documento`
--
ALTER TABLE `tipos_documento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`cedula`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `centro_id` (`centro_id`);

--
-- Indices de la tabla `videos_reporte`
--
ALTER TABLE `videos_reporte`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `centros_distribucion`
--
ALTER TABLE `centros_distribucion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inspecciones`
--
ALTER TABLE `inspecciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registros`
--
ALTER TABLE `registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_documento`
--
ALTER TABLE `tipos_documento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `videos_reporte`
--
ALTER TABLE `videos_reporte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`registro_id`) REFERENCES `registros` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registros`
--
ALTER TABLE `registros`
  ADD CONSTRAINT `fk_registros_centro` FOREIGN KEY (`centro_id`) REFERENCES `centros_distribucion` (`id`),
  ADD CONSTRAINT `fk_registros_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`centro_id`) REFERENCES `centros_distribucion` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
