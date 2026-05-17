<?php
// includes/auth.php
// ─────────────────────────────────────────────
//  Funciones de autenticación y sesión
// ─────────────────────────────────────────────

/**
 * Inicia sesión PHP de forma segura.
 * Llama esta función al inicio de cada página.
 */
function iniciarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false,   // true en HTTPS/producción
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

/**
 * Devuelve true si hay un usuario autenticado.
 */
function estaAutenticado(): bool {
    return isset($_SESSION['usuario_id']);
}

/**
 * Redirige al login si no hay sesión activa.
 */
function requiereAutenticacion(): void {
    if (!estaAutenticado()) {
        header('Location: login.php?error=sesion');
        exit;
    }
}

/**
 * Registra al usuario en la sesión tras login exitoso.
 */
function crearSesion(array $usuario): void {
    session_regenerate_id(true); // Previene session fixation
    $_SESSION['usuario_id']     = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];
}

/**
 * Destruye completamente la sesión.
 */
function destruirSesion(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Escapa HTML para prevenir XSS al mostrar datos.
 */
function e(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Valida formato de correo electrónico.
 */
function esCorreoValido(string $correo): bool {
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Genera un token CSRF y lo guarda en sesión.
 */
function generarCsrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica el token CSRF recibido por POST.
 */
function verificarCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Token CSRF inválido. Por favor recarga la página.');
    }
}
