<?php
// includes/config.php
// ─────────────────────────────────────────────
//  Configuración de conexión a la base de datos
// ─────────────────────────────────────────────

define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_auth');
define('DB_USER', 'root');       // Cambia si usas otro usuario en XAMPP
define('DB_PASS', '');           // XAMPP por defecto no tiene contraseña
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'Sistema de Autenticación');

function conectar(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            // En producción nunca mostrar detalles del error
            die('<p style="font-family:monospace;color:red">
                 Error de conexión a la base de datos. Verifica que XAMPP esté activo
                 y que la base de datos <strong>' . DB_NAME . '</strong> exista.</p>');
        }
    }
    return $pdo;
}
