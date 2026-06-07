<?php
// login.php — Página de acceso al sistema
if (session_status() === PHP_SESSION_NONE) session_start();

// Si ya tiene sesión, redirigir al dashboard
if (!empty($_SESSION['id_usuario'])) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Recolectora S.A.</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #F4F6F5;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: white;
            padding: 40px 36px;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            width: 360px;
            text-align: center;
        }
        .login-card h2  { color: #145c38; margin-bottom: 4px; }
        .login-card .subtitle { color: #888; font-size: 14px; margin-bottom: 28px; }
        input[type=email], input[type=password] {
            width: 100%; padding: 11px 14px;
            border: 1px solid #ddd; border-radius: 9px;
            font-size: 15px; margin-bottom: 14px;
            outline: none; transition: border .2s;
        }
        input:focus { border-color: #145c38; }
        button {
            width: 100%; padding: 12px;
            background: #145c38; color: white;
            border: none; border-radius: 9px;
            font-size: 15px; font-weight: 600;
            cursor: pointer; transition: background .2s;
        }
        button:hover { background: #0e4029; }
        .error { color: #c0392b; font-size: 13px; margin-bottom: 10px; }
        .logo { font-size: 42px; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="logo">🌿</div>
    <h2>Recolectora S.A.</h2>
    <p class="subtitle">Sistema de Control de Flotilla</p>

    <?php if (isset($_GET['error'])): ?>
        <p class="error">Credenciales incorrectas. Intente de nuevo.</p>
    <?php endif; ?>

    <form action="validar_login.php" method="POST">
        <input type="email"    name="email"    placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña"          required>
        <button type="submit">INICIAR SESIÓN</button>
    </form>
</div>
</body>
</html>
