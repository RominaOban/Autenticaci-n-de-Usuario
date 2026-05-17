<?php
// logout.php — Cierra la sesión y redirige al login
require_once 'includes/config.php';
require_once 'includes/auth.php';

iniciarSesion();
destruirSesion();

header('Location: login.php?logout=1');
exit;
