-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-05-2026 a las 23:59:44
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
(1, 2, 'LF0012', 1, '2026-04-09 17:35:15'),
(12, 15, 'KZWLN3K5', 1, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `id_comunicado` int(11) UNSIGNED NOT NULL,
  `id_comunidad` int(11) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `cuerpo` text NOT NULL,
  `tipo` enum('normal','urgente') NOT NULL DEFAULT 'normal',
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comunicados`
--

INSERT INTO `comunicados` (`id_comunicado`, `id_comunidad`, `titulo`, `cuerpo`, `tipo`, `fecha_publicacion`) VALUES
(2, 1, 'Corte de agua 9.00-21.00', 'xcv', 'normal', '2026-05-07 20:21:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicado_lectura`
--

CREATE TABLE `comunicado_lectura` (
  `id_comunicado` int(11) UNSIGNED NOT NULL,
  `id_usuario` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comunicado_lectura`
--

INSERT INTO `comunicado_lectura` (`id_comunicado`, `id_usuario`) VALUES
(2, 3);

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
(3, 1, 'Piscina', 30, 15, '09:00:00', '23:00:00', 30, 0, NULL, NULL),
(4, 1, 'Gym', 100, 20, '07:30:00', '21:45:00', 90, 0, NULL, NULL);

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
(6, 3, 'Seguir las ordenes de la administración Recio que bueno soy y que culito tengo'),
(7, 3, 'Cualquier duda a coque que se encarga de la piscina'),
(8, 4, 'zdgvd dsd');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_mensaje`
--

CREATE TABLE `foro_mensaje` (
  `id_mensaje` int(11) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `foro_mensaje`
--

INSERT INTO `foro_mensaje` (`id_mensaje`, `id_tema`, `id_usuario`, `mensaje`, `fecha_creacion`) VALUES
(4, 1, 1, 'Que te follen Pepe', '2026-05-02 13:45:36'),
(7, 1, 3, '[cita id=\"4\" autor=\"Maria Pelaez\"]Que te follen Pepe[/cita]\r\nCállese vieja bruja!! 🔥​🔥​🔥​🔥​​', '2026-05-02 16:26:26'),
(16, 3, 3, 'sadasfasdfasdfa', '2026-05-04 09:11:05'),
(17, 3, 3, '[cita id=\"16\" autor=\"pepe perez\"]sadasfasdfasdfa[/cita]\r\nasdfasdfasfdasdf', '2026-05-04 09:11:10'),
(18, 3, 1, 'hola', '2026-05-04 11:25:57'),
(19, 1, 1, 'hello', '2026-05-04 11:26:12'),
(20, 6, 1, 'asdasdas', '2026-05-04 11:26:34'),
(21, 6, 3, 'sisi', '2026-05-04 11:29:11'),
(22, 3, 3, '[cita id=\"16\" autor=\"pepe perez\"]sadasfasdfasdfa[/cita]\r\njhgj', '2026-05-04 11:53:05'),
(23, 8, 3, 'Otro éxito por la administración Recio que bueno soy y que culito tengo', '2026-05-06 18:52:50'),
(24, 8, 1, 'Antonio Recio dimisión', '2026-05-06 18:53:34'),
(25, 3, 1, '[cita id=\"17\" autor=\"pepe perez\"]asdfasdfasfdasdf[/cita]\r\nPues te conquisto el felpudo', '2026-05-06 18:54:11'),
(26, 3, 1, '[cita id=\"25\" autor=\"Maria Pelaez\"]Pues te conquisto el felpudo[/cita]\r\nEra broma', '2026-05-06 18:54:25'),
(27, 5, 1, 'Vendo movil de la rambla cogido del suelo', '2026-05-06 18:55:13'),
(28, 8, 3, '[cita id=\"24\" autor=\"Maria Pelaez\"]Antonio Recio dimisión[/cita]\r\n3 JUNTAS DE SUSPENSIÓN', '2026-05-06 18:55:56'),
(29, 5, 3, 'Vendo pistola electrica', '2026-05-06 18:56:32'),
(30, 5, 3, '[cita id=\"27\" autor=\"Maria Pelaez\"]Vendo movil de la rambla cogido del suelo[/cita]\r\nChoriza', '2026-05-06 18:56:40'),
(31, 5, 3, '[cita id=\"29\" autor=\"pepe perez\"]Vendo pistola electrica[/cita]\r\nDe primerísima calidad', '2026-05-06 19:05:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_tema`
--

CREATE TABLE `foro_tema` (
  `id_tema` int(11) NOT NULL,
  `id_comunidad` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estado` enum('abierto','cerrado') DEFAULT 'abierto',
  `categoria` varchar(50) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `foro_tema`
--

INSERT INTO `foro_tema` (`id_tema`, `id_comunidad`, `id_usuario`, `titulo`, `descripcion`, `fecha_creacion`, `estado`, `categoria`) VALUES
(1, 1, 3, 'Horario piscina', 'Alguien sabe el horario de la piscina?', '2026-04-29 13:16:30', 'abierto', 'general'),
(3, 1, 3, 'Busco pareja para league of legends', 'asdasdasdasdasd', '2026-05-04 09:10:47', 'abierto', 'general'),
(5, 1, 3, 'Vendo Boli Bic y regalo entradas para la Final de la Champions', 'Vendo boli Bic de color azul y regalo 2 entradas para la final de la Champions.\r\nPrecio: 1500 € (no negociable)', '2026-05-04 10:54:48', 'abierto', 'mercadillo'),
(6, 1, 1, 'prueba', 'asdasdasd', '2026-05-04 11:26:30', 'abierto', 'general'),
(8, 1, 3, 'Normas del presidente de dla comunidad', 'Acatar las normas de antonio recio presidente de la comunidad viva el rey y viva españa', '2026-05-06 18:52:12', 'abierto', 'convivencia');

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
(4, 3, 3, 15, '2026-04-23', '18:00:00', '18:30:00', 'inactivo'),
(5, 1, 3, 15, '2026-04-22', '18:00:00', '18:30:00', 'inactivo'),
(6, 1, 3, 14, '2026-04-23', '17:30:00', '18:00:00', 'inactivo'),
(7, 3, 3, 15, '2026-05-01', '16:30:00', '17:00:00', 'inactivo'),
(8, 1, 3, 15, '2026-05-01', '16:30:00', '17:00:00', 'inactivo'),
(9, 3, 3, 13, '2026-05-13', '16:30:00', '17:00:00', 'activo'),
(10, 3, 4, 18, '2026-05-02', '12:00:00', '13:30:00', 'inactivo');

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
  `rol` enum('vecino','presidente') NOT NULL,
  `token_recuperacion` varchar(100) DEFAULT NULL,
  `expiracion_token` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_vivienda`, `nombre`, `apellidos`, `dni`, `email`, `password`, `fecha_registro`, `es_propietario`, `rol`, `token_recuperacion`, `expiracion_token`) VALUES
(1, 2, 'Maria', 'Pelaez', '42034567R', 'mariapelaez@gmail.com', '$2y$10$XyV7m6OAZ8IdECKxvEYEt.7Waw1b4VNY4fNWGfHb6/p4rwdzWDjmS', '2026-04-09 17:37:13', 1, 'vecino', NULL, NULL),
(3, 4, 'pepe', 'perez', '28394873E', 'pepe@gmail.com', '$2y$10$qCYoCjYbtUazNP7W1pifwOFfHDJPT6A7bgGLqilyQ3STqWHwl1og.', '2026-04-15 02:18:47', 1, 'presidente', NULL, NULL),
(13, 15, 'Moisés', 'Moreno Robles', '23456789E', 'moisesmobles@gmail.com', '$2y$10$iT9DF1JRbEXIraF.2TEN1e14pNi3Srkj8B7Y51t.uhV7PfLoUVpSu', '2026-05-12 23:40:51', 1, 'vecino', NULL, NULL);

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
(4, 1, 'Planta 1 1ºC'),
(15, 1, 'Planta 2-2C');

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
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id_comunicado`),
  ADD KEY `id_comunidad` (`id_comunidad`);

--
-- Indices de la tabla `comunicado_lectura`
--
ALTER TABLE `comunicado_lectura`
  ADD PRIMARY KEY (`id_comunicado`,`id_usuario`),
  ADD KEY `id_comunicado` (`id_comunicado`),
  ADD KEY `id_usuario` (`id_usuario`);

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
-- Indices de la tabla `foro_mensaje`
--
ALTER TABLE `foro_mensaje`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `id_tema` (`id_tema`);

--
-- Indices de la tabla `foro_tema`
--
ALTER TABLE `foro_tema`
  ADD PRIMARY KEY (`id_tema`);

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
  MODIFY `id_codigo` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id_comunicado` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id_espacios_comunidad` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `espacios_normas`
--
ALTER TABLE `espacios_normas`
  MODIFY `id_espacios_normas` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `foro_mensaje`
--
ALTER TABLE `foro_mensaje`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `foro_tema`
--
ALTER TABLE `foro_tema`
  MODIFY `id_tema` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id_reservas` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `reunion`
--
ALTER TABLE `reunion`
  MODIFY `id_reunion` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  MODIFY `id_vivienda` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- Filtros para la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD CONSTRAINT `fk_comunicados_comunidad` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidad` (`id_comunidad`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `comunicado_lectura`
--
ALTER TABLE `comunicado_lectura`
  ADD CONSTRAINT `fk_lectura_comunicado` FOREIGN KEY (`id_comunicado`) REFERENCES `comunicados` (`id_comunicado`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lectura_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

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
