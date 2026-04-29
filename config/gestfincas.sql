-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-04-2026 a las 13:33:37
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
-- Base de datos: `gestfincas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_reunion`
--

CREATE TABLE `asistencia_reunion` (
  `id_asistencia` int(11) UNSIGNED NOT NULL,
  `id_reunion` int(11) UNSIGNED NOT NULL,
  `id_vivienda` int(10) UNSIGNED NOT NULL,
  `confirmacion` enum('pendiente','confirmada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_respuesta` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia_reunion`
--

INSERT INTO `asistencia_reunion` (`id_asistencia`, `id_reunion`, `id_vivienda`, `confirmacion`, `fecha_respuesta`) VALUES
(29, 5, 2, 'rechazada', '2026-04-15'),
(30, 5, 4, 'confirmada', '2026-04-16'),
(38, 7, 2, 'confirmada', '2026-04-17'),
(39, 7, 4, 'rechazada', '2026-04-17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigo_validacion`
--

CREATE TABLE `codigo_validacion` (
  `id_codigo` int(11) UNSIGNED NOT NULL,
  `id_vivienda` int(11) UNSIGNED NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `usado` tinyint(4) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `codigo_validacion`
--

INSERT INTO `codigo_validacion` (`id_codigo`, `id_vivienda`, `codigo`, `usado`, `fecha_creacion`) VALUES
(1, 2, 'LF0012', 1, '2026-04-09 17:35:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunidad`
--

CREATE TABLE `comunidad` (
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `id_mancomunidad` int(11) UNSIGNED DEFAULT NULL,
  `id_direccion` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comunidad`
--

INSERT INTO `comunidad` (`id_comunidad`, `id_mancomunidad`, `id_direccion`, `nombre`, `fecha_creacion`) VALUES
(1, NULL, 1, 'Las Flores', '2026-04-09 17:30:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuota`
--

CREATE TABLE `cuota` (
  `id_cuota` int(11) UNSIGNED NOT NULL,
  `id_vivienda` int(10) UNSIGNED NOT NULL,
  `tipo` enum('mensual','derrama') NOT NULL DEFAULT 'mensual',
  `concepto` varchar(255) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `fecha_emision` date NOT NULL,
  `estado` enum('pagada','pendiente') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cuota`
--

INSERT INTO `cuota` (`id_cuota`, `id_vivienda`, `tipo`, `concepto`, `importe`, `fecha_emision`, `estado`) VALUES
(1, 2, 'mensual', 'Cuota Marzo 2026', 50.00, '2026-03-01', 'pagada'),
(2, 4, 'mensual', 'Cuota Marzo 2026', 50.00, '2026-03-01', 'pagada'),
(3, 2, 'derrama', 'Derrama arreglo tejado', 100.00, '2026-03-15', 'pagada'),
(4, 4, 'derrama', 'Derrama arreglo tejado', 100.00, '2026-03-15', 'pagada'),
(13, 2, 'mensual', 'Cuota Marzo 2026', 50.00, '2026-03-01', 'pagada'),
(14, 4, 'mensual', 'Cuota Marzo 2026', 50.00, '2026-03-01', 'pagada'),
(17, 2, 'mensual', 'Ascensor reparación', 111.00, '2026-04-10', 'pagada'),
(18, 4, 'mensual', 'Ascensor reparación', 111.00, '2026-04-10', 'pagada'),
(19, 2, 'mensual', 'Cuota MAyo 2026', 66.00, '2026-04-30', 'pagada'),
(20, 4, 'mensual', 'Cuota MAyo 2026', 66.00, '2026-04-30', 'pagada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id_direccion` int(11) UNSIGNED NOT NULL,
  `tipo` enum('mancomunidad','comunidad','vivienda','') NOT NULL,
  `calle` varchar(100) NOT NULL,
  `numero` int(11) NOT NULL,
  `edificio` varchar(50) DEFAULT NULL,
  `planta` int(11) DEFAULT NULL,
  `puerta` varchar(10) DEFAULT NULL,
  `ciudad` varchar(50) NOT NULL,
  `provincia` varchar(50) NOT NULL,
  `codigo_postal` int(11) NOT NULL,
  `pais` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`id_direccion`, `tipo`, `calle`, `numero`, `edificio`, `planta`, `puerta`, `ciudad`, `provincia`, `codigo_postal`, `pais`) VALUES
(1, 'comunidad', 'Las Flores', 2, NULL, NULL, NULL, 'Berchules', 'Granada', 14700, 'España');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `espacios_comunidad`
--

CREATE TABLE `espacios_comunidad` (
  `id_espacios_comunidad` int(10) UNSIGNED NOT NULL,
  `id_comunidad` int(10) UNSIGNED NOT NULL,
  `nombre_espacio` varchar(100) NOT NULL,
  `aforo` int(4) UNSIGNED NOT NULL,
  `max_personas` int(4) UNSIGNED NOT NULL,
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  `duracion_uso` int(10) UNSIGNED NOT NULL,
  `bloqueado` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `espacios_comunidad`
--

INSERT INTO `espacios_comunidad` (`id_espacios_comunidad`, `id_comunidad`, `nombre_espacio`, `aforo`, `max_personas`, `hora_apertura`, `hora_cierre`, `duracion_uso`, `bloqueado`, `motivo`, `fecha_actualizacion`) VALUES
(3, 1, 'Piscina', 30, 15, '09:00:00', '23:00:00', 30, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `espacios_normas`
--

CREATE TABLE `espacios_normas` (
  `id_espacios_normas` int(10) UNSIGNED NOT NULL,
  `id_espacios_comunidad` int(10) UNSIGNED NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `espacios_normas`
--

INSERT INTO `espacios_normas` (`id_espacios_normas`, `id_espacios_comunidad`, `descripcion`) VALUES
(5, 3, 'Seguir las ordenes de la administración Recio que bueno soy y que culito tengo ');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gasto`
--

CREATE TABLE `gasto` (
  `id_gasto` int(11) UNSIGNED NOT NULL,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gasto`
--

INSERT INTO `gasto` (`id_gasto`, `id_comunidad`, `concepto`, `categoria`, `fecha`, `importe`, `estado`) VALUES
(1, 1, 'Revisión completa ascensor - presupuesto TechLift', 'Ascensores', '2026-03-20', 650.00, 'pendiente'),
(2, 1, 'Sustitución luminarias portal', 'Electricidad', '2026-03-16', 387.20, 'pendiente'),
(3, 1, 'Limpieza mensual marzo', 'Limpieza', '2026-03-01', 200.00, 'aprobado'),
(6, 1, 'Ascensor reparacion', 'Ascensores', '2026-04-24', 222.00, 'aprobado'),
(7, 1, 'devolucion ascensor', 'Ascensores', '2026-04-24', -100.00, 'aprobado'),
(8, 1, 'Limpieza', 'Limpieza', '2026-04-27', 100.00, 'aprobado'),
(9, 1, 'limpieza correccion', 'Mantenimiento y Reparaciones', '2026-04-27', -10.00, 'aprobado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id_incidencias` int(10) UNSIGNED NOT NULL,
  `id_vivienda` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `texto_normalizado` varchar(400) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` enum('pendiente','abierta','resuelta') NOT NULL,
  `numero_afectados` int(10) UNSIGNED NOT NULL,
  `foto_incidencia` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `incidencias`
--

INSERT INTO `incidencias` (`id_incidencias`, `id_vivienda`, `titulo`, `texto_normalizado`, `descripcion`, `fecha_creacion`, `fecha_actualizacion`, `estado`, `numero_afectados`, `foto_incidencia`) VALUES
(6, 4, 'Bombilla fundida', 'luz roto luz portal parpadea', 'La bombilla del portal parpadea', '2026-04-27 16:50:37', '2026-04-29 13:23:47', 'resuelta', 1, 'public/uploads/incidencias/inc_69ef77bd0254a1.10175354.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias_uniones`
--

CREATE TABLE `incidencias_uniones` (
  `id_incidencias` int(10) UNSIGNED NOT NULL,
  `id_vivienda` int(10) UNSIGNED NOT NULL,
  `fecha_union` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mancomunidad`
--

CREATE TABLE `mancomunidad` (
  `id_mancomunidad` int(11) UNSIGNED NOT NULL,
  `id_direccion` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reservas` int(11) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_espacios_comunidad` int(10) UNSIGNED NOT NULL,
  `asistentes` int(4) UNSIGNED NOT NULL DEFAULT 1,
  `fecha_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado_reserva` enum('activo','inactivo') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reservas`, `id_usuario`, `id_espacios_comunidad`, `asistentes`, `fecha_reserva`, `hora_inicio`, `hora_fin`, `estado_reserva`) VALUES
(1, 3, 3, 4, '2026-04-22', '10:00:00', '10:30:00', 'inactivo'),
(2, 1, 3, 13, '2026-04-21', '18:00:00', '18:30:00', 'inactivo'),
(3, 3, 3, 2, '2026-04-21', '18:30:00', '19:00:00', 'inactivo'),
(4, 3, 3, 15, '2026-04-23', '18:00:00', '18:30:00', 'activo'),
(5, 1, 3, 15, '2026-04-22', '18:00:00', '18:30:00', 'inactivo'),
(6, 1, 3, 14, '2026-04-23', '17:30:00', '18:00:00', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reunion`
--

CREATE TABLE `reunion` (
  `id_reunion` int(11) UNSIGNED NOT NULL,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `lugar` varchar(100) NOT NULL,
  `orden_del_dia` text NOT NULL,
  `estado` enum('convocada','en_curso','finalizada') NOT NULL DEFAULT 'convocada',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reunion`
--

INSERT INTO `reunion` (`id_reunion`, `id_comunidad`, `titulo`, `descripcion`, `fecha`, `hora`, `lugar`, `orden_del_dia`, `estado`, `fecha_creacion`) VALUES
(5, 1, 'ANTONIO RECIO PRESIDENTE', 'JUNTA URGENTE', '2026-07-24', '01:26:00', 'Casa de Antonio Recio', '[\"Punto del dia \",\"SOY PRESIDENTE DE LA COMUNIDAD\"]', 'convocada', '2026-04-15 02:22:07'),
(7, 1, 'asdddf', 'dfgdfghj', '2026-04-23', '12:12:00', 'asd', '[\"awsed\"]', 'convocada', '2026-04-17 09:12:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) UNSIGNED NOT NULL,
  `id_vivienda` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `es_propietario` tinyint(4) NOT NULL DEFAULT 1,
  `rol` enum('vecino','presidente') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_vivienda`, `nombre`, `apellidos`, `dni`, `email`, `password`, `fecha_registro`, `es_propietario`, `rol`) VALUES
(1, 2, 'Maria', 'Pelaez', '42034567R', 'mariapelaez@gmail.com', '$2y$10$XyV7m6OAZ8IdECKxvEYEt.7Waw1b4VNY4fNWGfHb6/p4rwdzWDjmS', '2026-04-09 17:37:13', 1, 'vecino'),
(3, 4, 'pepe', 'perez', '28394873E', 'pepe@gmail.com', '$2y$10$qCYoCjYbtUazNP7W1pifwOFfHDJPT6A7bgGLqilyQ3STqWHwl1og.', '2026-04-15 02:18:47', 1, 'presidente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vivienda`
--

CREATE TABLE `vivienda` (
  `id_vivienda` int(10) UNSIGNED NOT NULL,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vivienda`
--

INSERT INTO `vivienda` (`id_vivienda`, `id_comunidad`, `nombre`) VALUES
(2, 1, 'Planta 2-1C'),
(4, 1, 'Planta 1 1ºC');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `votacion`
--

CREATE TABLE `votacion` (
  `id_votacion` int(11) UNSIGNED NOT NULL,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_limite` datetime DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `votacion`
--

INSERT INTO `votacion` (`id_votacion`, `id_comunidad`, `titulo`, `descripcion`, `fecha_limite`, `fecha_creacion`, `activa`) VALUES
(1, 1, 'Presidente de la Comunidad', 'Votos a favor de Antonio Recio presidente, viva el rey y viva España!!!', '2026-04-23 15:08:00', '2026-04-16 15:08:42', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `votacion_opcion`
--

CREATE TABLE `votacion_opcion` (
  `id_opcion` int(11) UNSIGNED NOT NULL,
  `id_votacion` int(11) UNSIGNED NOT NULL,
  `texto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `votacion_opcion`
--

INSERT INTO `votacion_opcion` (`id_opcion`, `id_votacion`, `texto`) VALUES
(1, 1, 'Antonio Recio'),
(2, 1, 'Enrique Pastor'),
(3, 2, 'asd'),
(4, 2, 'dfg'),
(5, 3, 'dfg'),
(6, 3, 'asd'),
(7, 4, 'sdfzxc'),
(8, 4, 'zxc');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `voto`
--

CREATE TABLE `voto` (
  `id_voto` int(11) UNSIGNED NOT NULL,
  `id_votacion` int(11) UNSIGNED NOT NULL,
  `id_usuario` int(11) UNSIGNED NOT NULL,
  `id_opcion` int(11) UNSIGNED NOT NULL,
  `fecha_voto` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `voto`
--

INSERT INTO `voto` (`id_voto`, `id_votacion`, `id_usuario`, `id_opcion`, `fecha_voto`) VALUES
(1, 1, 3, 1, '2026-04-16 15:09:10'),
(2, 1, 1, 1, '2026-04-16 15:09:52'),
(3, 3, 3, 5, '2026-04-16 15:38:32'),
(4, 4, 3, 7, '2026-04-17 09:08:18'),
(5, 1, 5, 1, '2026-04-21 18:35:53');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencia_reunion`
--
ALTER TABLE `asistencia_reunion`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `uq_reunion_vivienda` (`id_reunion`,`id_vivienda`),
  ADD KEY `fk_asistencia_vivienda` (`id_vivienda`);

--
-- Indices de la tabla `codigo_validacion`
--
ALTER TABLE `codigo_validacion`
  ADD PRIMARY KEY (`id_codigo`),
  ADD KEY `id_vivienda` (`id_vivienda`);

--
-- Indices de la tabla `comunidad`
--
ALTER TABLE `comunidad`
  ADD PRIMARY KEY (`id_comunidad`),
  ADD KEY `id_mancomunidad` (`id_mancomunidad`),
  ADD KEY `id_direccion` (`id_direccion`);

--
-- Indices de la tabla `cuota`
--
ALTER TABLE `cuota`
  ADD PRIMARY KEY (`id_cuota`),
  ADD KEY `fk_cuota_vivienda` (`id_vivienda`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id_direccion`);

--
-- Indices de la tabla `espacios_comunidad`
--
ALTER TABLE `espacios_comunidad`
  ADD PRIMARY KEY (`id_espacios_comunidad`),
  ADD KEY `id_comunidad` (`id_comunidad`);

--
-- Indices de la tabla `espacios_normas`
--
ALTER TABLE `espacios_normas`
  ADD PRIMARY KEY (`id_espacios_normas`),
  ADD KEY `id_espacios_comunidad` (`id_espacios_comunidad`);

--
-- Indices de la tabla `gasto`
--
ALTER TABLE `gasto`
  ADD PRIMARY KEY (`id_gasto`),
  ADD KEY `fk_gasto_comunidad` (`id_comunidad`);

--
-- Indices de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD PRIMARY KEY (`id_incidencias`),
  ADD KEY `id_vivienda` (`id_vivienda`);
ALTER TABLE `incidencias` ADD FULLTEXT KEY `texto_normalizado` (`texto_normalizado`);

--
-- Indices de la tabla `incidencias_uniones`
--
ALTER TABLE `incidencias_uniones`
  ADD KEY `incidencias_uniones_ibfk_1` (`id_incidencias`),
  ADD KEY `incidencias_uniones_ibfk_2` (`id_vivienda`);

--
-- Indices de la tabla `mancomunidad`
--
ALTER TABLE `mancomunidad`
  ADD PRIMARY KEY (`id_mancomunidad`),
  ADD KEY `id_direccion` (`id_direccion`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reservas`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_espacios_comunidad` (`id_espacios_comunidad`);

--
-- Indices de la tabla `reunion`
--
ALTER TABLE `reunion`
  ADD PRIMARY KEY (`id_reunion`),
  ADD KEY `fk_reunion_comunidad` (`id_comunidad`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_vivienda` (`id_vivienda`);

--
-- Indices de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  ADD PRIMARY KEY (`id_vivienda`),
  ADD KEY `id_comunidad` (`id_comunidad`);

--
-- Indices de la tabla `votacion`
--
ALTER TABLE `votacion`
  ADD PRIMARY KEY (`id_votacion`),
  ADD KEY `id_comunidad` (`id_comunidad`);

--
-- Indices de la tabla `votacion_opcion`
--
ALTER TABLE `votacion_opcion`
  ADD PRIMARY KEY (`id_opcion`),
  ADD KEY `id_votacion` (`id_votacion`);

--
-- Indices de la tabla `voto`
--
ALTER TABLE `voto`
  ADD PRIMARY KEY (`id_voto`),
  ADD UNIQUE KEY `unique_voto_usuario` (`id_votacion`,`id_usuario`),
  ADD KEY `id_votacion` (`id_votacion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_opcion` (`id_opcion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia_reunion`
--
ALTER TABLE `asistencia_reunion`
  MODIFY `id_asistencia` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `codigo_validacion`
--
ALTER TABLE `codigo_validacion`
  MODIFY `id_codigo` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `comunidad`
--
ALTER TABLE `comunidad`
  MODIFY `id_comunidad` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cuota`
--
ALTER TABLE `cuota`
  MODIFY `id_cuota` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id_direccion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `espacios_comunidad`
--
ALTER TABLE `espacios_comunidad`
  MODIFY `id_espacios_comunidad` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `espacios_normas`
--
ALTER TABLE `espacios_normas`
  MODIFY `id_espacios_normas` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `gasto`
--
ALTER TABLE `gasto`
  MODIFY `id_gasto` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id_incidencias` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `mancomunidad`
--
ALTER TABLE `mancomunidad`
  MODIFY `id_mancomunidad` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reservas` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `reunion`
--
ALTER TABLE `reunion`
  MODIFY `id_reunion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  MODIFY `id_vivienda` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `votacion`
--
ALTER TABLE `votacion`
  MODIFY `id_votacion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `votacion_opcion`
--
ALTER TABLE `votacion_opcion`
  MODIFY `id_opcion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `voto`
--
ALTER TABLE `voto`
  MODIFY `id_voto` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencia_reunion`
--
ALTER TABLE `asistencia_reunion`
  ADD CONSTRAINT `fk_asistencia_reunion` FOREIGN KEY (`id_reunion`) REFERENCES `reunion` (`id_reunion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asistencia_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `codigo_validacion`
--
ALTER TABLE `codigo_validacion`
  ADD CONSTRAINT `fk_codigo_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `comunidad`
--
ALTER TABLE `comunidad`
  ADD CONSTRAINT `fk_comunidad_direccion` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comunidad_mancomunidad` FOREIGN KEY (`id_mancomunidad`) REFERENCES `mancomunidad` (`id_mancomunidad`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuota`
--
ALTER TABLE `cuota`
  ADD CONSTRAINT `fk_cuota_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gasto`
--
ALTER TABLE `gasto`
  ADD CONSTRAINT `fk_gasto_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD CONSTRAINT `fk_incidencias_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `incidencias_uniones`
--
ALTER TABLE `incidencias_uniones`
  ADD CONSTRAINT `incidencias_uniones_ibfk_1` FOREIGN KEY (`id_incidencias`) REFERENCES `incidencias` (`id_incidencias`) ON DELETE CASCADE,
  ADD CONSTRAINT `incidencias_uniones_ibfk_2` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mancomunidad`
--
ALTER TABLE `mancomunidad`
  ADD CONSTRAINT `fk_mancomunidad_direccion` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reunion`
--
ALTER TABLE `reunion`
  ADD CONSTRAINT `fk_reunion_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_vivienda` FOREIGN KEY (`id_vivienda`) REFERENCES `vivienda` (`id_vivienda`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vivienda`
--
ALTER TABLE `vivienda`
  ADD CONSTRAINT `fk_vivienda_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
