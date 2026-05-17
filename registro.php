<?php
// registro.php — Registro de nuevos usuarios
require_once 'includes/config.php';
require_once 'includes/auth.php';

iniciarSesion();

// Si ya está autenticado, ir al perfil
if (estaAutenticado()) {
    header('Location: perfil.php');
    exit;
}

$errores  = [];
$exito    = '';
$valores  = ['cedula' => '', 'nombre' => '', 'correo' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $cedula   = trim($_POST['cedula']   ?? '');
    $nombre   = trim($_POST['nombre']  ?? '');
    $correo   = trim($_POST['correo']  ?? '');
    $password = $_POST['password']     ?? '';
    $confirm  = $_POST['confirmar']    ?? '';

    $valores = compact('cedula', 'nombre', 'correo');

    // ── Validaciones ──────────────────────────────
    if ($cedula === '') {
        $errores[] = 'La cédula es obligatoria.';
    } elseif (!preg_match('/^\d{6,20}$/', $cedula)) {
        $errores[] = 'La cédula solo puede contener dígitos (6-20 caracteres).';
    }

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    } elseif (strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }

    if ($correo === '') {
        $errores[] = 'El correo es obligatorio.';
    } elseif (!esCorreoValido($correo)) {
        $errores[] = 'El formato del correo no es válido.';
    }

    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if ($password !== $confirm) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    // ── Verificar correo y cédula ────────────────
    if (empty($errores)) {
        $pdo = conectar();

        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            $errores[] = 'El correo ya está registrado.';
        }
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE cedula = ? LIMIT 1');
        $stmt->execute([$cedula]);
        if ($stmt->fetch()) {
            $errores[] = 'La cédula ya está registrada.';
        }
    }

    // ── Insertar usuario ──────────────────────────
    if (empty($errores)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (cedula, nombre, correo, password) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$cedula, $nombre, $correo, $hash]);

        $exito = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
        $valores = ['cedula' => '', 'nombre' => '', 'correo' => ''];
    }
}

$csrf = generarCsrf();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="css/estilo.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="page-wrapper">

  <div class="card">
    <h1 class="card-title">Crear cuenta</h1>
    <p class="card-subtitle">Completa los datos para registrarte.</p>

    <?php if ($exito): ?>
      <div class="alert alert-success">
        <span class="alert-icon">
          <i class="fa-solid fa-circle-check"></i>
        </span>
        <?= e($exito) ?>
        <a href="login.php" class="link" style="margin-left:.5rem">Ir al login →</a>
      </div>
    <?php endif; ?>

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

    <form method="POST" action="registro.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

      <div class="form-group">
        <label for="cedula">Cédula</label>
        <input type="text" id="cedula" name="cedula"
               value="<?= e($valores['cedula']) ?>"
               placeholder="Ej: 1234567890" autocomplete="off" required>
      </div>

      <div class="form-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre"
               value="<?= e($valores['nombre']) ?>"
               placeholder="Tu nombre" autocomplete="name" required>
      </div>

      <div class="form-group">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo"
               value="<?= e($valores['correo']) ?>"
               placeholder="correo@ejemplo.com" autocomplete="email" required>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
        <div class="strength-bar"><div class="strength-fill" id="sbar"></div></div>
        <p class="field-hint" id="strength-text"></p>
      </div>

      <div class="form-group">
        <label for="confirmar">Confirmar contraseña</label>
        <input type="password" id="confirmar" name="confirmar"
               placeholder="Confirmar contraseña" autocomplete="new-password" required>
      </div>

      <button type="submit" class="btn btn-primary">Crear cuenta</button>
    </form>

    <p class="text-center mt-2" style="font-size:.88rem;color:var(--text-muted)">
      ¿Ya tienes cuenta? <a href="login.php" class="link">Inicia sesión</a>
    </p>
  </div>

</div>

<script>

// =============================================
// Indicador de fortaleza
// =============================================

const pwInput = document.getElementById('password');

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

  if (v.length >= 8)          s++;
  if (/[A-Z]/.test(v))        s++;
  if (/[0-9]/.test(v))        s++;
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

</script>
</body>
</html>
