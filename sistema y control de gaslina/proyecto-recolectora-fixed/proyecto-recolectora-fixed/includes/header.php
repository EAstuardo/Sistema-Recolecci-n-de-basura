<?php
/**
 * includes/header.php
 * Header, sidebar y navegación principal
 */

// IMPORTANTE: Incluir auth.php ANTES de usar getCurrentUser()
require_once __DIR__ . '/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser();

// Determinar si estamos en pages/ o en la raíz
$base_path = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo h($page_title ?? 'Sistema de Recolección'); ?> | AgroGestor GT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/styles.css">
</head>
<body>

<!-- Overlay para cerrar sidebar en móvil -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <span class="logo-icon">♻️</span>
            <span class="logo-text">Agro<span>Gestor</span> GT</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?php echo $base_path; ?>index.php" class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <div class="nav-divider">Gestión</div>
        
        <a href="<?php echo $base_path; ?>pages/colonias.php" class="nav-item <?php echo $current_page == 'colonias.php' ? 'active' : ''; ?>">
            <span class="nav-icon">🏘️</span>
            <span class="nav-text">Colonias</span>
        </a>
        
        <a href="<?php echo $base_path; ?>pages/clientes.php" class="nav-item <?php echo $current_page == 'clientes.php' ? 'active' : ''; ?>">
            <span class="nav-icon">👥</span>
            <span class="nav-text">Clientes</span>
        </a>
        
        <a href="<?php echo $base_path; ?>pages/clientes_lista.php" class="nav-item <?php echo $current_page == 'clientes_lista.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📋</span>
            <span class="nav-text">Lista de Clientes</span>
        </a>
        
        <a href="<?php echo $base_path; ?>pages/pagos.php" class="nav-item <?php echo $current_page == 'pagos.php' ? 'active' : ''; ?>">
            <span class="nav-icon">💰</span>
            <span class="nav-text">Registrar Pago</span>
        </a>
        
        <a href="<?php echo $base_path; ?>pages/recibos.php" class="nav-item <?php echo $current_page == 'recibos.php' ? 'active' : ''; ?>">
            <span class="nav-icon">🧾</span>
            <span class="nav-text">Recibos</span>
        </a>
        
        <?php if (isAdmin()): ?>
            <a href="<?php echo $base_path; ?>pages/usuarios.php" class="nav-item <?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-text">Usuarios</span>
            </a>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <div class="theme-toggle">
            <button id="themeToggle" class="theme-btn">
                <span class="theme-icon">🌙</span>
                <span class="theme-text">Modo Oscuro</span>
            </button>
        </div>
        
        <div class="user-info">
            <div class="user-name">
                <?php echo $user ? h($user['nombre']) : 'Invitado'; ?>
                <span class="user-role"><?php echo $user ? h($user['rol']) : ''; ?></span>
            </div>
            <a href="<?php echo $base_path; ?>logout.php" class="logout-btn">
                <span>🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>
</aside>

<main class="main-content">
    <!-- Barra superior móvil con botón hamburguesa -->
    <div class="mobile-topbar">
        <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Abrir menú">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <span class="mobile-title"><?php echo h($page_title ?? 'AgroGestor GT'); ?></span>
    </div>

    <div class="content-header">
        <h1><?php echo h($page_title ?? 'Dashboard'); ?></h1>
        <p class="subtitle"><?php echo $page_subtitle ?? 'Bienvenido al sistema de gestión'; ?></p>
    </div>
    
    <div class="toast-container" id="toastContainer"></div>
