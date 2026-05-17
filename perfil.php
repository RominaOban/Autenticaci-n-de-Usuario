<?php
// perfil.php — Zona privada: ver y actualizar datos del usuario

require_once 'includes/config.php';
require_once 'includes/auth.php';

iniciarSesion();
requiereAutenticacion();

// Conexión a la base de datos
$pdo = conectar();

// Obtener datos del usuario actual
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

// Si el usuario no existe, cerrar sesión
if (!$usuario) {
    destruirSesion();
    header('Location: login.php');
    exit;
}

$errores = [];
$exito   = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verificarCsrf();

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    // ── Validaciones ─────────────────────────────

    if ($nombre === '') {
        $errores[] = 'El nombre no puede estar vacío.';
    } elseif (strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }

    if ($correo === '') {
        $errores[] = 'El correo no puede estar vacío.';
    } elseif (!esCorreoValido($correo)) {
        $errores[] = 'El formato del correo no es válido.';
    }

    // Verificar si otro usuario ya usa ese correo
    if (empty($errores) && $correo !== $usuario['correo']) {

        $check = $pdo->prepare(
            'SELECT id FROM usuarios WHERE correo = ? AND id != ? LIMIT 1'
        );

        $check->execute([$correo, $usuario['id']]);

        if ($check->fetch()) {
            $errores[] = 'Ese correo ya está en uso por otra cuenta.';
        }
    }

    // Actualizar datos
    if (empty($errores)) {

        $upd = $pdo->prepare(
            'UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?'
        );

        $upd->execute([
            $nombre,
            $correo,
            $usuario['id']
        ]);

        // Actualizar sesión
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_correo'] = $correo;

        // Recargar datos actualizados
        $reload = $pdo->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
        $reload->execute([$usuario['id']]);
        $usuario = $reload->fetch();

        $exito = 'Perfil actualizado correctamente.';
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
        Mi perfil — <?= SITE_NAME ?>
    </title>

    <!-- Estilos -->
    <link rel="stylesheet" href="css/estilo.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<!-- ── Navbar ───────────────────────────────── -->

<nav class="navbar">

    <a href="perfil.php" class="navbar-brand">
        Mi perfil
    </a>

    <div class="navbar-user">

        <!-- Avatar -->
        <div class="avatar">
            <?= e($inicial) ?>
        </div>

        <!-- Nombre -->
        <span>
            <?= e($usuario['nombre']) ?>
        </span>

        <!-- Botón salir -->
        <a href="logout.php"
           class="btn btn-danger btn-sm">

            Salir
        </a>

    </div>

</nav>

<!-- ── Contenido principal ─────────────────── -->

<div class="profile-page">

    <!-- Tarjeta usuario -->
    <div class="user-badge">

        <div class="avatar"
             style="width:52px;height:52px;border-radius:14px;font-size:1.2rem">

            <?= e($inicial) ?>

        </div>

        <div class="user-badge-info">

            <h3>
                <?= e($usuario['nombre']) ?>
            </h3>

            <p>
                <?= e($usuario['correo']) ?>
                ·
                Cédula:
                <?= e($usuario['cedula']) ?>
            </p>

            <p class="field-hint">
                Registrado el
                <?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?>
            </p>

        </div>

    </div>

    <!-- Tabs -->
    <div class="tabs">

        <a href="perfil.php"
           class="tab-link active">

            Datos del perfil
        </a>

        <a href="cambiar_password.php"
           class="tab-link">

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

    <!-- Mensajes error -->
    <?php if ($errores): ?>

        <div class="alert alert-error">

            <span class="alert-icon">
                <i class="fa-solid fa-circle-xmark"></i>
            </span>

            <ul style="list-style:none;padding:0;margin:0">

                <?php foreach ($errores as $err): ?>

                    <li>
                        <?= e($err) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>

    <!-- Card principal -->
    <div class="card card-wide">

        <div class="section-header">

            <h2>
                Información personal
            </h2>

            <p>
                Actualiza tu nombre y correo electrónico.
            </p>

        </div>

        <!-- Formulario -->
        <form method="POST"
              action="perfil.php"
              novalidate>

            <!-- Token -->
            <input type="hidden"
                   name="csrf_token"
                   value="<?= e($csrf) ?>">

            <!-- Cédula -->
            <div class="form-group">

                <label for="cedula_ro">
                    Cédula
                </label>

                <input type="text"
                       id="cedula_ro"
                       value="<?= e($usuario['cedula']) ?>"
                       disabled
                       style="opacity:.5;cursor:not-allowed">

                <p class="field-hint">
                    La cédula no puede modificarse.
                </p>

            </div>

            <!-- Nombre -->
            <div class="form-group">

                <label for="nombre">
                    Nombre completo
                </label>

                <input type="text"
                       id="nombre"
                       name="nombre"
                       value="<?= e($usuario['nombre']) ?>"
                       placeholder="Tu nombre"
                       required>

            </div>

            <!-- Correo -->
            <div class="form-group">

                <label for="correo">
                    Correo electrónico
                </label>

                <input type="email"
                       id="correo"
                       name="correo"
                       value="<?= e($usuario['correo']) ?>"
                       placeholder="correo@ejemplo.com"
                       required>

            </div>

            <!-- Botones -->
            <div class="btn-row">

                <button type="submit"
                        class="btn btn-primary">

                    Guardar cambios
                </button>

                <a href="cambiar_password.php"
                   class="btn btn-secondary">

                    Cambiar contraseña
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>
