<?php
// validar_login.php — Procesa el formulario de login
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    header('Location: login.php?error=1');
    exit;
}

// Consulta usando prepared statement (segura contra SQL injection)
$stmt = $pdo->prepare(
    "SELECT id_usuario, nombre, rol, password FROM usuarios WHERE email = ? AND activo = 1"
);
$stmt->execute([$email]);
$usuario = $stmt->fetch();

// Soporte para passwords planas (legacy) y bcrypt
$valid = $usuario && (
    password_verify($password, $usuario['password']) ||   // bcrypt (Cesar)
    $usuario['password'] === $password                     // plain text (legacy Wendy)
);

if ($valid) {
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre']     = $usuario['nombre'];
    $_SESSION['rol']        = $usuario['rol'];

    // Redirigir según rol
    if ($usuario['rol'] === 'ADMIN') {
        header('Location: admin.php');
    } else {
        header('Location: colaborador.php');
    }
    exit;
}

header('Location: login.php?error=1');
exit;
