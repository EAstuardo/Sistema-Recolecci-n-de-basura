<?php
/**
 * cobrador.php
 * Redirige al dashboard (el rol se maneja internamente)
 */

session_start();

// Si no está logueado, ir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Redirigir al dashboard
header('Location: index.php');
exit();