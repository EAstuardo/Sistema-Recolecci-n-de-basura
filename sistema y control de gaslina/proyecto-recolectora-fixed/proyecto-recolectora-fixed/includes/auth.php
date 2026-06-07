<?php
/**
 * includes/auth.php
 * Verificación de autenticación y roles
 */

session_start();

// Verificar si el usuario está logueado
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Verificar rol específico
function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'ADMIN') {
        header('Location: index.php?error=no_autorizado');
        exit();
    }
}

// Verificar si es administrador
function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN';
}

// Obtener usuario actual
function getCurrentUser(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id = ? AND activo = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}