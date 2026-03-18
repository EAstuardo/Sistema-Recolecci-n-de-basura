<?php
// Entrypoint del dashboard | Diseño + estilos (sin lógica aún)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FLOTA Sync - Dashboard</title>

  <!-- Iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-L+z7VBDXc5tXHr8Yzq7a+E9IDExD7u7WQzJtH8rVG2h4MjwWJ4m4+h4Fdo7edQ8sovv+1Fm23dJv/cdYFMFZZw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Estilos -->
  <link rel="stylesheet" href="css/estilos_base.css">
  <link rel="stylesheet" href="css/diseño_responsivo.css">
</head>
<body class="app">
  <aside class="sidebar">
    <div class="sidebar__brand">
      <span class="sidebar__logo">🚛</span>
      <div>
        <h1 class="sidebar__title">FLOTA<span>Sync</span></h1>
        <p class="sidebar__subtitle">Panel</p>
      </div>
    </div>

    <nav class="sidebar__nav">
      <a href="#" class="sidebar__link active"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
      <a href="#" class="sidebar__link"><i class="fa-solid fa-truck"></i><span>Vehículos</span></a>
      <a href="#" class="sidebar__link"><i class="fa-solid fa-gas-pump"></i><span>Combustible</span></a>
      <a href="#" class="sidebar__link"><i class="fa-solid fa-route"></i><span>Rutas</span></a>
      <a href="#" class="sidebar__link"><i class="fa-solid fa-user"></i><span>Conductores</span></a>
      <a href="#" class="sidebar__link"><i class="fa-solid fa-chart-line"></i><span>Reportes</span></a>
      <a href="#" class="sidebar__link"><i class="fa-solid fa-bell"></i><span>Alertas</span></a>
      <a href="#" class="sidebar__link"><i class="fa-solid fa-gear"></i><span>Configuración</span></a>
    </nav>

    <div class="sidebar__footer">
      <div class="sidebar__profile">
        <div class="avatar">A</div>
        <div>
          <p class="profile__name">Admin</p>
          <p class="profile__role">Superusuario</p>
        </div>
      </div>
      <button class="btn btn--ghost btn--full"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión</button>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <div class="search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" placeholder="Buscar vehículo, ruta o conductor..." aria-label="Buscar">
      </div>

      <div class="topbar__actions">
        <div class="topbar__item">
          <span class="topbar__date">24 Abr, 2024</span>
        </div>
        <button class="icon-btn" aria-label="Notificaciones">
          <i class="fa-solid fa-bell"></i>
          <span class="badge">3</span>
        </button>
        <button class="icon-btn" aria-label="Perfil">
          <i class="fa-solid fa-circle-user"></i>
        </button>
      </div>
    </header>

    <section class="panel panel--overview">
      <div class="panel__header">
        <h2>Dashboard</h2>
        <p>Resumen de la operación del día</p>
      </div>

      <div class="cards">
        <article class="card card--primary">
          <div class="card__title">
            <p>Gasto en Gasolina Hoy</p>
            <span class="card__icon"><i class="fa-solid fa-gas-pump"></i></span>
          </div>
          <div class="card__value">$152,300</div>
          <div class="card__note card__note--success"><i class="fa-solid fa-arrow-up"></i> 12% vs ayer</div>
        </article>

        <article class="card card--success">
          <div class="card__title">
            <p>Vehículos en Operación</p>
            <span class="card__icon"><i class="fa-solid fa-truck-field"></i></span>
          </div>
          <div class="card__value">85</div>
          <div class="card__note card__note--success"><i class="fa-solid fa-arrow-up"></i> 5 vs ayer</div>
        </article>

        <article class="card card--info">
          <div class="card__title">
            <p>Consumo Promedio</p>
            <span class="card__icon"><i class="fa-solid fa-droplet"></i></span>
          </div>
          <div class="card__value">32.5 L/veh</div>
          <div class="card__note card__note--danger"><i class="fa-solid fa-arrow-down"></i> 2.3%</div>
        </article>

        <article class="card card--warning">
          <div class="card__title">
            <p>Vehículos Fuera de Rango</p>
            <span class="card__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
          </div>
          <div class="card__value">5</div>
          <div class="card__note card__note--danger"><i class="fa-solid fa-arrow-down"></i> 1 vs ayer</div>
        </article>
      </div>
    </section>

    <section class="panel panel--map">
      <div class="panel__header panel__header--split">
        <div>
          <h3>Ubicación de Unidades Activas</h3>
          <p class="muted">En tiempo real</p>
        </div>
        <div class="panel__actions">
          <button class="btn btn--outline"><i class="fa-solid fa-filter"></i> Filtrar por fecha</button>
          <button class="btn btn--primary"><i class="fa-solid fa-file-export"></i> Exportar Reporte</button>
        </div>
      </div>

      <div class="map-placeholder">
        <div class="map-placeholder__overlay">
          <div class="status">
            <h4>Estado de Unidades</h4>
            <ul class="status__list">
              <li><span class="dot dot--success"></span> En ruta (45)</li>
              <li><span class="dot dot--warning"></span> Detenido (20)</li>
              <li><span class="dot dot--danger"></span> Combustible bajo (10)</li>
              <li><span class="dot dot--info"></span> Mantenimiento (5)</li>
            </ul>
          </div>
          <div class="alerts">
            <h4>Alertas Recientes</h4>
            <ul class="alerts__list">
              <li>
                <span class="alert alert--danger"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div>
                  <strong>Unidad 07</strong>
                  <span>Consumo alto</span>
                  <span class="muted">Hace 5 min</span>
                </div>
              </li>
              <li>
                <span class="alert alert--success"><i class="fa-solid fa-check-circle"></i></span>
                <div>
                  <strong>Unidad 12</strong>
                  <span>Ruta finalizada</span>
                  <span class="muted">Hace 12 min</span>
                </div>
              </li>
              <li>
                <span class="alert alert--warning"><i class="fa-solid fa-wrench"></i></span>
                <div>
                  <strong>Unidad 03</strong>
                  <span>Mantenimiento</span>
                  <span class="muted">Hace 1 hora</span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </main>
</body>
</html>