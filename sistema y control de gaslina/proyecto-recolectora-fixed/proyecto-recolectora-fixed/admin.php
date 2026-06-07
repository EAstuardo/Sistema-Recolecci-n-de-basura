<?php
/**
 * admin.php
 * Redirige al dashboard (el rol se maneja internamente)
 */

session_start();

// Si no está logueado, ir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Si no es admin, mostrar error
if ($_SESSION['user_role'] !== 'ADMIN') {
    header('Location: index.php?error=no_autorizado');
    exit();
}

// Redirigir al dashboard
header('Location: index.php');
exit();