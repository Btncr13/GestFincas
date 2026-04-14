-- phpMyAdmin SQL Dump
-- version 5.2.1
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-04-2026
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- 1. DESTRUCCIÓN Y CREACIÓN LIMPIA DE LA BASE DE DATOS
-- --------------------------------------------------------
DROP DATABASE IF EXISTS `gestfincas`;
CREATE DATABASE `gestfincas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gestfincas`;

-- --------------------------------------------------------
-- 2. ESTRUCTURA DE TABLAS Y VOLCADO DE DATOS
-- --------------------------------------------------------

-- Tabla: direccion
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
  `codigo_postal` int(11) NOT NULL,
  `pais` varchar(50) NOT NULL,
  PRIMARY KEY (`id_direccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `direccion` (`id_direccion`, `tipo`, `calle`, `numero`, `edificio`, `planta`, `puerta`, `ciudad`, `provincia`, `codigo_postal`, `pais`) VALUES
(1, 'comunidad', 'Las Flores', 2, NULL, NULL, NULL, 'Berchules', 'Granada', 14700, 'España');

-- --------------------------------------------------------

-- Tabla: mancomunidad
CREATE TABLE `mancomunidad` (
  `id_mancomunidad` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_direccion` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_mancomunidad`),
  KEY `idx_direccion` (`id_direccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Tabla: comunidad
CREATE TABLE `comunidad` (
  `id_comunidad` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_mancomunidad` int(11) UNSIGNED DEFAULT NULL,
  `id_direccion` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_comunidad`),
  KEY `idx_mancomunidad` (`id_mancomunidad`),
  KEY `idx_direccion` (`id_direccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `comunidad` (`id_comunidad`, `id_mancomunidad`, `id_direccion`, `nombre`, `fecha_creacion`) VALUES
(1, NULL, 1, 'Las Flores', '2026-04-09 17:30:25');

-- --------------------------------------------------------

-- Tabla: vivienda
CREATE TABLE `vivienda` (
  `id_vivienda` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_vivienda`),
  KEY `idx_comunidad` (`id_comunidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `vivienda` (`id_vivienda`, `id_comunidad`, `nombre`) VALUES
(2, 1, 'Planta 2-1B');

-- --------------------------------------------------------

-- Tabla: codigo_validacion
CREATE TABLE `codigo_validacion` (
  `id_codigo` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vivienda` int(11) UNSIGNED NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_codigo`),
  UNIQUE KEY `uk_codigo` (`codigo`),
  KEY `idx_vivienda` (`id_vivienda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `codigo_validacion` (`id_codigo`, `id_vivienda`, `codigo`, `usado`, `fecha_creacion`) VALUES
(1, 2, 'LF0012', 1, '2026-04-09 17:35:15');

-- --------------------------------------------------------

-- Tabla: usuario
CREATE TABLE `usuario` (
  `id_usuario` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vivienda` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `es_propietario` tinyint(1) NOT NULL DEFAULT 1,
  `rol` enum('vecino','presidente') NOT NULL DEFAULT 'vecino',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_dni` (`dni`),
  KEY `idx_vivienda` (`id_vivienda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuario` (`id_usuario`, `id_vivienda`, `nombre`, `apellidos`, `dni`, `email`, `password`, `fecha_registro`, `es_propietario`, `rol`) VALUES
(1, 2, 'Maria', 'Pelaez', '42034567R', 'mariapelaez@gmail.com', '$2y$10$XyV7m6OAZ8IdECKxvEYEt.7Waw1b4VNY4fNWGfHb6/p4rwdzWDjmS', '2026-04-09 17:37:13', 1, 'vecino'),
(2, 2, 'Pepe', 'Jimenez', '762394987', 'pepe@gmail.com', '$2y$10$2GA5jd/jkChFIHxe5Z4kXOw5FAuO79BiTgM1ia14mkKaYem9U0Hzi', '2026-04-10 12:53:48', 1, 'presidente');

-- --------------------------------------------------------
-- 3. RESTRICCIONES DE CLAVES FORÁNEAS (RELACIONES)
-- --------------------------------------------------------

ALTER TABLE `codigo_validacion`
  ADD CONSTRAINT `fk_codigo_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `comunidad`
  ADD CONSTRAINT `fk_comunidad_direccion` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comunidad_mancomunidad` FOREIGN KEY (`id_mancomunidad`) REFERENCES `mancomunidad` (`id_mancomunidad`) ON UPDATE CASCADE;

ALTER TABLE `mancomunidad`
  ADD CONSTRAINT `fk_mancomunidad_direccion` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`) ON UPDATE CASCADE;

ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `vivienda`
  ADD CONSTRAINT `fk_vivienda_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;