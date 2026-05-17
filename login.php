<?php
// login.php — Inicio de sesión
require_once 'includes/config.php';
require_once 'includes/auth.php';

iniciarSesion();

if (estaAutenticado()) {
    header('Location: perfil.php');
    exit;
}

$error  = '';
$correo = '';

// Mensaje de advertencia (sesión expirada o acceso denegado)
$info = match($_GET['error'] ?? '') {
    'sesion'  => 'Tu sesión expiró. Inicia sesión nuevamente.',
    'acceso'  => 'Debes iniciar sesión para acceder a esa página.',
    default   => ''
};

// Mensaje de éxito al cerrar sesión
$infoExito = (($_GET['sesion'] ?? '') === 'cerrada')
    ? 'Sesión cerrada correctamente.'
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validación básica
    if ($correo === '' || $password === '') {
        $error = 'Ingresa tu correo y contraseña.';
    } elseif (!esCorreoValido($correo)) {
        $error = 'El formato del correo no es válido.';
    } else {

        // Buscar usuario
        $pdo  = conectar();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            crearSesion($usuario);
            header('Location: perfil.php');
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}

$csrf = generarCsrf();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Iniciar sesión — <?= SITE_NAME ?></title>

  <!-- Estilos -->
  <link rel="stylesheet" href="css/estilo.css">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<div class="page-wrapper">

  <div class="card">

    <h1 class="card-title">Bienvenido</h1>
    <p class="card-subtitle">
      Ingresa tus credenciales para continuar.
    </p>

    <!-- Sesión cerrada -->
    <?php if ($infoExito): ?>
      <div class="alert alert-success">
        <span class="alert-icon">
          <i class="fa-solid fa-circle-check"></i>
        </span>
        <?= e($infoExito) ?>
      </div>
    <?php endif; ?>

    <!-- Mensaje informativo (sesión expirada / acceso denegado) -->
    <?php if ($info): ?>
      <div class="alert alert-warning">

        <span class="alert-icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </span>

        <?= e($info) ?>
      </div>
    <?php endif; ?>

    <!-- Error -->
    <?php if ($error): ?>
      <div class="alert alert-error">

        <span class="alert-icon">
          <i class="fa-solid fa-circle-xmark"></i>
        </span>

        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <!-- Registro exitoso -->
    <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
      <div class="alert alert-success">

        <span class="alert-icon">
          <i class="fa-solid fa-circle-check"></i>
        </span>

        Registro exitoso. Ahora puedes iniciar sesión.
      </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST" action="login.php" novalidate>

      <input type="hidden"
             name="csrf_token"
             value="<?= e($csrf) ?>">

      <!-- Correo -->
      <div class="form-group">

        <label for="correo">Correo electrónico</label>

        <input type="email"
               id="correo"
               name="correo"
               value="<?= e($correo) ?>"
               placeholder="correo@ejemplo.com"
               autocomplete="email"
               autofocus
               required>
      </div>

      <!-- Contraseña -->
      <div class="form-group">

        <label for="password">Contraseña</label>

        <input type="password"
               id="password"
               name="password"
               placeholder="••••••••"
               autocomplete="current-password"
               required>
      </div>

      <!-- Botón -->
      <button type="submit"
              class="btn btn-primary"
              style="margin-top:.5rem">

        Iniciar sesión
      </button>

    </form>

    <div class="divider">o</div>

    <a href="registro.php" class="btn btn-secondary">
      Crear nueva cuenta
    </a>

  </div>

</div>

</body>
</html>
