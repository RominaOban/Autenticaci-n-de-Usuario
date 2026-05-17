<?php
// cambiar_password.php — Cambio seguro de contraseña

require_once 'includes/config.php';
require_once 'includes/auth.php';

iniciarSesion();
requiereAutenticacion();

$pdo = conectar();

// ─────────────────────────────────────────────
// Cargar datos del usuario
// ─────────────────────────────────────────────
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['usuario_id']]);

$usuario = $stmt->fetch();

if (!$usuario) {
    destruirSesion();
    header('Location: login.php');
    exit;
}

$errores = [];
$exito   = '';

// ─────────────────────────────────────────────
// Procesar formulario
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verificarCsrf();

    $actual    = $_POST['password_actual'] ?? '';
    $nueva     = $_POST['password_nueva'] ?? '';
    $confirmar = $_POST['password_confirmar'] ?? '';

    // Validar campos vacíos
    if ($actual === '' || $nueva === '' || $confirmar === '') {
        $errores[] = 'Todos los campos son obligatorios.';
    }

    // Verificar contraseña actual
    if (empty($errores)) {

        if (!password_verify($actual, $usuario['password'])) {
            $errores[] = 'La contraseña actual no es correcta.';
        }
    }

    // Validaciones de nueva contraseña
    if (empty($errores)) {

        if (strlen($nueva) < 8) {
            $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }

        if ($nueva === $actual) {
            $errores[] = 'La nueva contraseña debe ser diferente a la actual.';
        }

        if ($nueva !== $confirmar) {
            $errores[] = 'La nueva contraseña y la confirmación no coinciden.';
        }
    }

    // Actualizar contraseña
    if (empty($errores)) {

        $hash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]);

        $upd = $pdo->prepare(
            'UPDATE usuarios SET password = ? WHERE id = ?'
        );

        $upd->execute([$hash, $usuario['id']]);

        $exito = '¡Contraseña actualizada correctamente!';
    }
}

// Token CSRF
$csrf = generarCsrf();

// Inicial para avatar
$inicial = strtoupper(
    mb_substr($usuario['nombre'], 0, 1)
);
?>

<!DOCTYPE html>
<html lang="es">

<head>

  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <title>
    Cambiar contraseña — <?= SITE_NAME ?>
  </title>

  <!-- Estilos -->
  <link rel="stylesheet" href="css/estilo.css">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<!-- ────────────────────────────────────────── -->
<!-- Navbar -->
<!-- ────────────────────────────────────────── -->

<nav class="navbar">

  <a href="perfil.php" class="navbar-brand">
    Seguridad
  </a>

  <div class="navbar-user">

    <div class="avatar">
      <?= e($inicial) ?>
    </div>

    <span>
      <?= e($usuario['nombre']) ?>
    </span>

    <a href="logout.php"
       class="btn btn-danger btn-sm">

      Salir
    </a>

  </div>

</nav>

<!-- ────────────────────────────────────────── -->
<!-- Contenido -->
<!-- ────────────────────────────────────────── -->

<div class="profile-page">

  <!-- Tabs -->
  <div class="tabs">

    <a href="perfil.php"
       class="tab-link">

      Datos del perfil
    </a>

    <a href="cambiar_password.php"
       class="tab-link active">

      Cambiar contraseña
    </a>

  </div>

  <!-- Mensaje éxito -->
  <?php if ($exito): ?>

    <div class="alert alert-success">

      <span class="alert-icon">
        <i class="fa-solid fa-circle-check"></i>
      </span>

      <?= e($exito) ?>

    </div>

  <?php endif; ?>

  <!-- Errores -->
  <?php if ($errores): ?>

    <div class="alert alert-error">

      <span class="alert-icon">
        <i class="fa-solid fa-circle-xmark"></i>
      </span>

      <ul style="list-style:none;padding:0;margin:0">

        <?php foreach ($errores as $err): ?>

          <li><?= e($err) ?></li>

        <?php endforeach; ?>

      </ul>

    </div>

  <?php endif; ?>

  <!-- Card -->
  <div class="card card-wide">

    <div class="section-header">

      <h2>Cambiar contraseña</h2>

      <p>
        Ingresa tu contraseña actual y luego la nueva contraseña.
      </p>

    </div>

    <!-- Formulario -->
    <form method="POST"
          action="cambiar_password.php"
          novalidate>

      <input type="hidden"
             name="csrf_token"
             value="<?= e($csrf) ?>">

      <!-- Contraseña actual -->
      <div class="form-group">

        <label for="password_actual">
          Contraseña actual
        </label>

        <input type="password"
               id="password_actual"
               name="password_actual"
               placeholder="Tu contraseña actual"
               autocomplete="current-password"
               required>

      </div>

      <!-- Nueva contraseña -->
      <div class="form-group">

        <label for="password_nueva">
          Nueva contraseña
        </label>

        <input type="password"
               id="password_nueva"
               name="password_nueva"
               placeholder="Mínimo 8 caracteres"
               autocomplete="new-password"
               required>

        <!-- Barra visual -->
        <div class="strength-bar">
          <div class="strength-fill" id="sbar"></div>
        </div>

        <!-- Texto fuerza -->
        <p class="field-hint" id="strength-text"></p>

      </div>

      <!-- Confirmar -->
      <div class="form-group">

        <label for="password_confirmar">
          Confirmar nueva contraseña
        </label>

        <input type="password"
               id="password_confirmar"
               name="password_confirmar"
               placeholder="Repite la nueva contraseña"
               autocomplete="new-password"
               required>

        <p class="field-hint" id="match-text"></p>

      </div>

      <!-- Botones -->
      <div class="btn-row">

        <button type="submit"
                class="btn btn-primary">

          Actualizar contraseña
        </button>

        <a href="perfil.php"
           class="btn btn-secondary">

          Cancelar
        </a>

      </div>

    </form>

  </div>

</div>

<!-- ────────────────────────────────────────── -->
<!-- JavaScript -->
<!-- ────────────────────────────────────────── -->

<script>

// =============================================
// Indicador de fortaleza
// =============================================

const pwInput = document.getElementById('password_nueva');

const bar = document.getElementById('sbar');

const strText = document.getElementById('strength-text');

const labels = [
  'Muy débil',
  'Débil',
  'Aceptable',
  'Fuerte'
];

const colors = [
  '#f87171',
  '#fbbf24',
  '#34d399',
  '#5b7cfa'
];

pwInput.addEventListener('input', function () {

  const v = this.value;

  let s = 0;

  if (v.length >= 8) s++;

  if (/[A-Z]/.test(v)) s++;

  if (/[0-9]/.test(v)) s++;

  if (/[^A-Za-z0-9]/.test(v)) s++;

  // Cambiar ancho barra
  bar.style.width = (s * 25) + '%';

  // Cambiar color
  bar.style.background =
    colors[s - 1] || 'var(--border)';

  // Texto descriptivo
  strText.textContent =
    v.length ? labels[s - 1] || '' : '';

  strText.style.color =
    colors[s - 1] || 'var(--text-muted)';

});


// =============================================
// Verificar coincidencia
// =============================================

document
  .getElementById('password_confirmar')
  .addEventListener('input', function () {

    const matchText =
      document.getElementById('match-text');

    // Vacío
    if (this.value === '') {

      matchText.textContent = '';

      return;
    }

    // Coinciden
    if (this.value === pwInput.value) {

      matchText.innerHTML =
        '<i class="fa-solid fa-circle-check"></i> Las contraseñas coinciden';

      matchText.style.color = '#34d399';

    } else {

      // No coinciden
      matchText.innerHTML =
        '<i class="fa-solid fa-circle-xmark"></i> No coinciden';

      matchText.style.color = '#f87171';
    }

});

</script>

</body>
</html>