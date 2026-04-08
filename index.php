<?php
// Puedes agregar aquí la lógica de sesión/autenticación si es necesario
?>
<!DOCTYPE html>
<html lang="es">
<head>
            <script>
            // Recargar la página si se accede mediante el historial (navegador atrás)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
            </script>
        <script>
        // Forzar recarga si el tema cambió al volver con history.back
        (function() {
            const temaActual = localStorage.getItem('tema-recolectora') || 'light';
            const temaGuardado = sessionStorage.getItem('tema-recolectora-ultima') || '';
            if (temaGuardado && temaGuardado !== temaActual) {
                sessionStorage.setItem('tema-recolectora-ultima', temaActual);
                location.reload();
            } else {
                sessionStorage.setItem('tema-recolectora-ultima', temaActual);
            }
        })();
        </script>
    <meta charset="UTF-8">
    <title>Dashboard | Recolectora S A</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS base y responsive -->
    <link rel="stylesheet" href="Developer-6  Reportes y UI General/interfaz/css/estilos_base.css">
    <link rel="stylesheet" href="Developer-6  Reportes y UI General/interfaz/css/diseño_responsivo.css">
    <link rel="stylesheet" href="Developer-6  Reportes y UI General/interfaz/css/modo_claro.css">
    <link rel="stylesheet" href="Developer-6  Reportes y UI General/interfaz/css/modo_oscuro.css">
    <script src="Developer-6  Reportes y UI General/interfaz/js/alternarTema.js"></script>
</head>
<body>
    <!-- Navbar superior -->
    <header class="navbar">
        <div class="navbar-left">
            <img src="Developer-6  Reportes y UI General/interfaz/imagenes/logo.jpg" alt="Logo" class="logo">
            <span class="titulo-app">Recolectora S A</span>
        </div>
        <div class="navbar-center">
            <input type="search" placeholder="Buscar vehículo, ruta o conductor..." class="buscador">
        </div>
        <div class="navbar-right">
           
            <span class="icono-alerta"></span>
            <span class="icono-usuario"></span>
        </div>
    </header>

    <!-- Menú lateral (responsive) -->
    <nav id="menu-lateral" class="menu-lateral">
        <?php include "Developer-6  Reportes y UI General/interfaz/componentes/menu_lateral.php"; ?>
    </nav>
    <button class="btn-menu-movil" onclick="toggleMenuLateral()">
        <img src="Developer-6  Reportes y UI General/interfaz/imagenes/menu-icon.png" alt="Menú">
    </button>

    <!-- Panel principal -->
    <main class="panel-principal">
        <section class="dashboard-header">
            <h1>Dashboard</h1>
            <span>Resumen de la operación del día</span>
            <!-- Botón de 3 puntos para exportar -->
            <div class="menu-opciones">
                <button id="btn-opciones" onclick="toggleOpciones()">...</button>
                <div id="opciones-dropdown" class="dropdown-opciones" style="display:none;">
                    <button onclick="exportarPDF()">Exportar en PDF</button>
                    <button onclick="exportarExcel()">Exportar en Excel</button>
                    <button onclick="window.location.href='Developer-6  Reportes y UI General/interfaz/configuracion.php'">Configuración</button>
                </div>
            </div>
        </section>

        <!-- Tarjetas KPI -->
        <section class="tarjetas-kpi">
            <?php include "Developer-6  Reportes y UI General/interfaz/componentes/tarjetas_kpi.php"; ?>
        </section>

        <!-- Mapa de unidades activas -->
        <section class="mapa-unidades">
           <div class="mapa-panel">
    <h3>Ubicación de Unidades Activas</h3>
    <button class="btn-ver-mapa" onclick="window.location.href='mapa.php'">
        Ver Mapa Completo
        <!-- ... -->
<div id="mapa-real" style="margin-top:1px; height:10px; border-radius:5px;"></div>
    </button>
    <!-- ... -->
<script src="Developer-6  Reportes y UI General/interfaz/js/mapa.js"></script>
        </section>
        <br> </br>

        <!-- Lista de alertas -->
        <section class="alertas-recientes">
            <?php include "Developer-6  Reportes y UI General/interfaz/componentes/lista_alertas.php"; ?>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <span>&copy; <?php echo date("Y"); ?> Sistema de Recolección de Desechos Sólidos</span>
    </footer>

    <!-- JS para alternar menú -->
    <script>
        function toggleOpciones() {
            var dropdown = document.getElementById('opciones-dropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
        function toggleMenuLateral() {
            var menu = document.getElementById('menu-lateral');
            menu.classList.toggle('abierto');
        }
        // Cierra el menú de opciones al hacer clic fuera
        document.addEventListener('click', function(event) {
            var opciones = document.getElementById('opciones-dropdown');
            var btn = document.getElementById('btn-opciones');
            if (!btn.contains(event.target) && !opciones.contains(event.target)) {
                opciones.style.display = 'none';
            }
        });
    </script>
</body>
</html>