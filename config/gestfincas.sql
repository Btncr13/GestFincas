-- --------------------------------------------------------
-- BASE DE DATOS: `gestfincas` - OPTIMIZADA
-- --------------------------------------------------------
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. CREACIÓN DE TABLAS
-- --------------------------------------------------------

CREATE TABLE `asistencia_reunion` (
  `id_asistencia` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_reunion` int(11) UNSIGNED NOT NULL,
  `id_vivienda` int(10) UNSIGNED NOT NULL,
  `confirmacion` enum('pendiente','confirmada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_respuesta` date DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `uq_reunion_vivienda` (`id_reunion`,`id_vivienda`),
  KEY `fk_asistencia_vivienda` (`id_vivienda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `codigo_validacion` (
  `id_codigo` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vivienda` int(11) UNSIGNED NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_codigo`),
  UNIQUE KEY `uq_codigo` (`codigo`),
  KEY `id_vivienda` (`id_vivienda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comunidad` (
  `id_comunidad` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_mancomunidad` int(11) UNSIGNED DEFAULT NULL,
  `id_direccion` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_comunidad`),
  KEY `id_mancomunidad` (`id_mancomunidad`),
  KEY `id_direccion` (`id_direccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `direccion` (
  `id_direccion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo` enum('mancomunidad','comunidad','vivienda','') NOT NULL,
  `calle` varchar(100) NOT NULL,
  `numero` int(11) NOT NULL,
  `edificio` varchar(50) DEFAULT NULL,
  `planta` int(11) DEFAULT NULL,
  `puerta` varchar(10) DEFAULT NULL,
  `ciudad` varchar(50) NOT NULL,
  `provincia` varchar(50) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `pais` varchar(50) NOT NULL,
  PRIMARY KEY (`id_direccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mancomunidad` (
  `id_mancomunidad` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_direccion` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_mancomunidad`),
  KEY `id_direccion` (`id_direccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reunion` (
  `id_reunion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `lugar` varchar(100) NOT NULL,
  `orden_del_dia` text NOT NULL CHECK (json_valid(`orden_del_dia`)),
  `estado` enum('convocada','en_curso','finalizada') NOT NULL DEFAULT 'convocada',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reunion`),
  KEY `fk_reunion_comunidad` (`id_comunidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuario` (
  `id_usuario` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vivienda` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `es_propietario` tinyint(1) NOT NULL DEFAULT 1,
  `rol` enum('vecino','presidente') NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `id_vivienda` (`id_vivienda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vivienda` (
  `id_vivienda` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_vivienda`),
  UNIQUE KEY `uq_comunidad_vivienda` (`id_comunidad`,`nombre`),
  KEY `id_comunidad` (`id_comunidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `votacion` (
  `id_votacion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_limite` datetime DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_votacion`),
  KEY `id_comunidad` (`id_comunidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `votacion_opcion` (
  `id_opcion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_votacion` int(11) UNSIGNED NOT NULL,
  `texto` varchar(255) NOT NULL,
  PRIMARY KEY (`id_opcion`),
  KEY `id_votacion` (`id_votacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `voto` (
  `id_voto` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_votacion` int(11) UNSIGNED NOT NULL,
  `id_usuario` int(11) UNSIGNED NOT NULL,
  `id_opcion` int(11) UNSIGNED NOT NULL,
  `fecha_voto` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_voto`),
  UNIQUE KEY `unique_voto_usuario` (`id_votacion`,`id_usuario`),
  KEY `id_votacion` (`id_votacion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_opcion` (`id_opcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- 2. RESTRICCIONES (CLAVES FORÁNEAS)
-- --------------------------------------------------------

ALTER TABLE `asistencia_reunion`
  ADD CONSTRAINT `fk_asistencia_reunion` FOREIGN KEY (`id_reunion`) REFERENCES `reunion` (`id_reunion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asistencia_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `codigo_validacion`
  ADD CONSTRAINT `fk_codigo_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `comunidad`
  ADD CONSTRAINT `fk_comunidad_direccion` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comunidad_mancomunidad` FOREIGN KEY (`id_mancomunidad`) REFERENCES `mancomunidad` (`id_mancomunidad`) ON UPDATE CASCADE;

ALTER TABLE `mancomunidad`
  ADD CONSTRAINT `fk_mancomunidad_direccion` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`) ON UPDATE CASCADE;

ALTER TABLE `reunion`
  ADD CONSTRAINT `fk_reunion_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `vivienda`
  ADD CONSTRAINT `fk_vivienda_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ¡NUEVAS RESTRICCIONES PARA EL MÓDULO DE VOTACIONES QUE FALTABAN! --
ALTER TABLE `votacion`
  ADD CONSTRAINT `fk_votacion_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `votacion_opcion`
  ADD CONSTRAINT `fk_opcion_votacion` FOREIGN KEY (`id_votacion`) REFERENCES `votacion` (`id_votacion`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `voto`
  ADD CONSTRAINT `fk_voto_votacion` FOREIGN KEY (`id_votacion`) REFERENCES `votacion` (`id_votacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_voto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_voto_opcion` FOREIGN KEY (`id_opcion`) REFERENCES `votacion_opcion` (`id_opcion`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;