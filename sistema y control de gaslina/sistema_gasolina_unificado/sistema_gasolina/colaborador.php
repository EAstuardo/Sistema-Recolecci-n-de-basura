<?php
// colaborador.php — Vista para cobrador/operador
require_once __DIR__ . '/config/auth.php';
require_auth(); // cualquier rol autenticado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel — Recolectora S.A.</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #F4F6F5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; padding: 40px; border-radius: 14px; box-shadow: 0 8px 24px rgba(0,0,0,.1); text-align: center; }
        h1 { color: #145c38; margin-bottom: 10px; }
        p { color: #666; }
        a { color: #145c38; }
    </style>
</head>
<body>
<div class="card">
    <h1>🌿 Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
    <p>Rol: <strong><?= htmlspecialchars($_SESSION['rol']) ?></strong></p>
    <br>
    <p><a href="logout.php">Cerrar sesión</a></p>
</div>
</body>
</html>
