-- ============================================
--  SISTEMA DE AUTENTICACIÓN - Base de Datos
--  Ejecutar en phpMyAdmin
-- ============================================

CREATE DATABASE IF NOT EXISTS sistema_auth
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sistema_auth;

CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    cedula        VARCHAR(20)     NOT NULL,
    nombre        VARCHAR(100)    NOT NULL,
    correo        VARCHAR(150)    NOT NULL,
    password      VARCHAR(255)    NOT NULL,
    fecha_registro DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_correo  (correo),
    UNIQUE KEY uq_cedula  (cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Usuario de prueba (password: Test1234!)
-- -----------------------------------------------
INSERT INTO usuarios (cedula, nombre, correo, password) VALUES
(
    '1234567890',
    'Usuario Demo',
    'demo@correo.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);
-- NOTA: El hash de arriba corresponde a la contraseña "password"
-- Para producción, registra tu propio usuario desde el formulario.

DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    cedula         VARCHAR(20)     NOT NULL,
    nombre         VARCHAR(100)    NOT NULL,
    correo         VARCHAR(150)    NOT NULL,
    password       VARCHAR(255)    NOT NULL,
    fecha_registro DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_correo (correo),
    UNIQUE KEY uq_cedula (cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
