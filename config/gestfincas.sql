-- --------------------------------------------------------
-- DESTRUCCIÓN Y CREACIÓN LIMPIA DE LA BASE DE DATOS
-- --------------------------------------------------------
DROP DATABASE IF EXISTS `gestfincas`;
CREATE DATABASE `gestfincas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gestfincas`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. CREACIÓN DE TABLAS
-- --------------------------------------------------------

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

CREATE TABLE `vivienda` (
  `id_vivienda` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_vivienda`),
  UNIQUE KEY `uq_comunidad_vivienda` (`id_comunidad`,`nombre`),
  KEY `id_comunidad` (`id_comunidad`)
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

CREATE TABLE `espacios_comunidad` (
  `id_espacios_comunidad` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `nombre_espacio` varchar(100) NOT NULL,
  `aforo` int(4) UNSIGNED NOT NULL,
  `max_personas` int(4) UNSIGNED NOT NULL,
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  `duracion_uso` int(10) UNSIGNED NOT NULL,
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_espacios_comunidad`),
  KEY `id_comunidad` (`id_comunidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `espacios_normas` (
  `id_espacios_normas` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_espacios_comunidad` int(10) UNSIGNED NOT NULL,
  `descripcion` text NOT NULL,
  PRIMARY KEY (`id_espacios_normas`),
  KEY `id_espacios_comunidad` (`id_espacios_comunidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservas` (
  `id_reservas` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) UNSIGNED NOT NULL,
  `id_espacios_comunidad` int(10) UNSIGNED NOT NULL,
  `asistentes` int(4) UNSIGNED NOT NULL DEFAULT 1,
  `fecha_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado_reserva` enum('activo','inactivo') NOT NULL,
  PRIMARY KEY (`id_reservas`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_espacios_comunidad` (`id_espacios_comunidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLAS MÓDULO COMUNICACIONES
CREATE TABLE `comunicados` (
  `id_comunicado` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `cuerpo` text NOT NULL,
  `tipo` enum('normal','urgente') NOT NULL DEFAULT 'normal',
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_comunicado`),
  KEY `id_comunidad` (`id_comunidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comunicado_lectura` (
  `id_comunicado` int(11) UNSIGNED NOT NULL,
  `id_usuario` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_comunicado`,`id_usuario`),
  KEY `id_comunicado` (`id_comunicado`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. VOLCADO DE DATOS (MOCK DATA)
-- --------------------------------------------------------

INSERT INTO `direccion` (`id_direccion`, `tipo`, `calle`, `numero`, `ciudad`, `provincia`, `codigo_postal`, `pais`) VALUES 
(1, 'comunidad', 'Las Flores', 2, 'Berchules', 'Granada', '14700', 'España');

INSERT INTO `comunidad` (`id_comunidad`, `id_direccion`, `nombre`, `fecha_creacion`) VALUES 
(1, 1, 'Las Flores', '2026-04-09 17:30:25');

INSERT INTO `vivienda` (`id_vivienda`, `id_comunidad`, `nombre`) VALUES 
(2, 1, 'Planta 2-1B'), 
(4, 1, 'Planta 1 1ºC');

INSERT INTO `usuario` (`id_usuario`, `id_vivienda`, `nombre`, `apellidos`, `dni`, `email`, `password`, `fecha_registro`, `es_propietario`, `rol`) VALUES 
(1, 2, 'Maria', 'Pelaez', '42034567R', 'mariapelaez@gmail.com', '$2y$10$XyV7m6OAZ8IdECKxvEYEt.7Waw1b4VNY4fNWGfHb6/p4rwdzWDjmS', '2026-04-09 17:37:13', 1, 'vecino'),
(3, 4, 'pepe', 'perez', '28394873E', 'pepe@gmail.com', '$2y$10$qCYoCjYbtUazNP7W1pifwOFfHDJPT6A7bgGLqilyQ3STqWHwl1og.', '2026-04-15 02:18:47', 1, 'presidente');

INSERT INTO `codigo_validacion` (`id_codigo`, `id_vivienda`, `codigo`, `usado`, `fecha_creacion`) VALUES 
(1, 2, 'LF0012', 1, '2026-04-09 17:35:15');

INSERT INTO `reunion` (`id_reunion`, `id_comunidad`, `titulo`, `descripcion`, `fecha`, `hora`, `lugar`, `orden_del_dia`, `estado`, `fecha_creacion`) VALUES 
(5, 1, 'ANTONIO RECIO PRESIDENTE', 'JUNTA URGENTE', '2026-07-24', '01:26:00', 'Casa de Antonio Recio', '["Punto del dia ","SOY PRESIDENTE DE LA COMUNIDAD"]', 'convocada', '2026-04-15 02:22:07'), 
(7, 1, 'asdddf', 'dfgdfghj', '2026-04-23', '12:12:00', 'asd', '["awsed"]', 'convocada', '2026-04-17 09:12:22');

INSERT INTO `asistencia_reunion` (`id_asistencia`, `id_reunion`, `id_vivienda`, `confirmacion`, `fecha_respuesta`) VALUES 
(29, 5, 2, 'rechazada', '2026-04-15'), 
(30, 5, 4, 'confirmada', '2026-04-16'), 
(38, 7, 2, 'confirmada', '2026-04-17'), 
(39, 7, 4, 'rechazada', '2026-04-17');

INSERT INTO `votacion` (`id_votacion`, `id_comunidad`, `titulo`, `descripcion`, `fecha_limite`, `fecha_creacion`, `activa`) VALUES 
(1, 1, 'Presidente de la Comunidad', 'Votos a favor de Antonio Recio presidente, viva el rey y viva España!!!', '2026-04-23 15:08:00', '2026-04-16 15:08:42', 1);

INSERT INTO `votacion_opcion` (`id_opcion`, `id_votacion`, `texto`) VALUES 
(1, 1, 'Antonio Recio'), 
(2, 1, 'Enrique Pastor');

INSERT INTO `voto` (`id_voto`, `id_votacion`, `id_usuario`, `id_opcion`, `fecha_voto`) VALUES 
(1, 1, 3, 1, '2026-04-16 15:09:10'), 
(2, 1, 1, 1, '2026-04-16 15:09:52');

INSERT INTO `espacios_comunidad` (`id_espacios_comunidad`, `id_comunidad`, `nombre_espacio`, `aforo`, `max_personas`, `hora_apertura`, `hora_cierre`, `duracion_uso`, `bloqueado`, `motivo`) VALUES 
(3, 1, 'Piscina', 30, 15, '09:00:00', '23:00:00', 30, 0, NULL);

INSERT INTO `reservas` (`id_reservas`, `id_usuario`, `id_espacios_comunidad`, `asistentes`, `fecha_reserva`, `hora_inicio`, `hora_fin`, `estado_reserva`) VALUES 
(1, 3, 3, 4, '2026-04-22', '10:00:00', '10:30:00', 'activo'), 
(2, 1, 3, 13, '2026-04-21', '18:00:00', '18:30:00', 'activo'), 
(3, 3, 3, 2, '2026-04-21', '18:30:00', '19:00:00', 'activo'), 
(4, 3, 3, 15, '2026-04-23', '18:00:00', '18:30:00', 'activo'), 
(5, 1, 3, 15, '2026-04-22', '18:00:00', '18:30:00', 'activo');


-- --------------------------------------------------------
-- 3. RESTRICCIONES (CLAVES FORÁNEAS)
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

ALTER TABLE `votacion`
  ADD CONSTRAINT `fk_votacion_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `votacion_opcion`
  ADD CONSTRAINT `fk_opcion_votacion` FOREIGN KEY (`id_votacion`) REFERENCES `votacion` (`id_votacion`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `voto`
  ADD CONSTRAINT `fk_voto_votacion` FOREIGN KEY (`id_votacion`) REFERENCES `votacion` (`id_votacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_voto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_voto_opcion` FOREIGN KEY (`id_opcion`) REFERENCES `votacion_opcion` (`id_opcion`) ON DELETE CASCADE ON UPDATE CASCADE;

-- NUEVAS CLAVES FORÁNEAS MÓDULO ESPACIOS Y RESERVAS
ALTER TABLE `espacios_comunidad`
  ADD CONSTRAINT `fk_espacios_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `espacios_normas`
  ADD CONSTRAINT `fk_normas_espacios` FOREIGN KEY (`id_espacios_comunidad`) REFERENCES `espacios_comunidad` (`id_espacios_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reservas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reservas_espacios` FOREIGN KEY (`id_espacios_comunidad`) REFERENCES `espacios_comunidad` (`id_espacios_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;


-- CLAVES FORÁNEAS MÓDULO COMUNICACIONES
ALTER TABLE `comunicados`
  ADD CONSTRAINT `fk_comunicados_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `comunicado_lectura`
  ADD CONSTRAINT `fk_lectura_comunicado` FOREIGN KEY (`id_comunicado`) REFERENCES `comunicados` (`id_comunicado`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lectura_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;