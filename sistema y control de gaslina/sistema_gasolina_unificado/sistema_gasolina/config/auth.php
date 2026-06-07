<?php
// config/auth.php — middleware de sesión
// Incluir ANTES de cualquier output en páginas protegidas.
// Uso: require_once __DIR__ . '/../config/auth.php';
// O con rol específico: require_once_auth('ADMIN');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica que haya sesión activa y, opcionalmente, que el rol coincida.
 * Redirige a login.php si no se cumple.
 */
function require_auth(string $rol = ''): void {
    if (empty($_SESSION['id_usuario'])) {
        header('Location: /login.php');
        exit;
    }
    if ($rol && ($_SESSION['rol'] ?? '') !== $rol) {
        // Usuario autenticado pero sin el rol requerido
        http_response_code(403);
        exit('Acceso denegado.');
    }
}

/**
 * Verifica que el usuario sea ADMIN.
 * Equivalente a require_auth('ADMIN') pero más explícito en los formularios.
 */
function solo_admin(): void {
    require_auth('ADMIN');
}
