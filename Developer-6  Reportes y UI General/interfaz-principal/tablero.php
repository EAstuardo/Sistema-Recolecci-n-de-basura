<?php
// Dashboard (tablero) usando componentes para mantener el HTML organizado
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FLOTA Sync - Dashboard</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-L+z7VBDXc5tXHr8Yzq7a+E9IDExD7u7WQzJtH8rVG2h4MjwWJ4m4+h4Fdo7edQ8sovv+1Fm23dJv/cdYFMFZZw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="css/estilos_base.css">
  <link rel="stylesheet" href="css/diseño_responsivo.css">
</head>
<body class="app">
  <?php include __DIR__ . '/componentes/menu_lateral.php'; ?>

  <main class="main">
    <?php include __DIR__ . '/componentes/cabecera.php'; ?>

    <section class="panel panel--overview">
      <?php include __DIR__ . '/componentes/tarjetas_kpi.php'; ?>
    </section>

    <section class="panel panel--map">
      <?php include __DIR__ . '/componentes/mapa.php'; ?>
    </section>
  </main>
</body>
</html>
