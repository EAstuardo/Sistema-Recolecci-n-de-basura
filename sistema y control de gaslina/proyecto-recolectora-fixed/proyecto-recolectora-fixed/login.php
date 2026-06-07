<?php
/**
 * login.php
 * Página de inicio de sesión
 */

session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema de Recolección</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            padding: 20px;
        }
        .login-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo .logo-text {
            font-size: 28px;
            font-weight: 700;
        }
        .login-logo .logo-text span {
            color: var(--primary);
        }
        .login-title {
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 8px;
        }
        .login-subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 32px;
        }
        .error-message {
            background: rgba(244,67,54,0.1);
            border: 1px solid rgba(244,67,54,0.3);
            color: var(--danger);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .demo-info {
            margin-top: 24px;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: 10px;
            font-size: 13px;
        }
        .demo-info p {
            margin: 4px 0;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-text">Agro<span>Gestor</span> GT</div>
            </div>
            <h1 class="login-title">Bienvenido</h1>
            <p class="login-subtitle">Ingresa tus credenciales</p>
            
            <?php if ($error === '1'): ?>
                <div class="error-message">
                    ❌ Correo o contraseña incorrectos
                </div>
            <?php elseif ($error === 'no_autorizado'): ?>
                <div class="error-message">
                    ⚠️ No tienes permisos para acceder a esta sección
                </div>
            <?php endif; ?>
            
            <form action="api/auth.php" method="POST">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="admin@recolectora.com">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </form>
            
            <div class="demo-info">
                <p><strong>📋 Credenciales de prueba:</strong></p>
                <p>Administrador: admin@recolectora.com / password</p>
                <p>Cobrador: cobrador@recolectora.com (registrar en DB)</p>
            </div>
        </div>
    </div>
</body>
</html>