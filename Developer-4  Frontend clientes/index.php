<?php
// ══════════════════════════════════════
//  index.php — Dashboard
// ══════════════════════════════════════
require_once 'includes/db.php';
$titulo_pagina = 'Dashboard';

$db = getDB();

// ── Estadísticas ─────────────────────
$total_colonias = (int) $db->query("SELECT COUNT(*) FROM colonias")->fetchColumn();
$total_clientes = (int) $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$total_activos  = (int) $db->query("SELECT COUNT(*) FROM clientes WHERE estatus = 'activo'")->fetchColumn();
$total_pend     = (int) $db->query("SELECT COUNT(*) FROM clientes WHERE estatus = 'pendiente'")->fetchColumn();

// ── Último cliente ───────────────────
$ultimo = $db->query(
    "SELECT c.nombre, c.apellido, col.nombre AS colonia
     FROM clientes c
     JOIN colonias col ON col.id = c.colonia_id
     ORDER BY c.creado_en DESC
     LIMIT 1"
)->fetch();

// ── Últimos 5 clientes ───────────────
$recientes = $db->query(
    "SELECT c.nombre, c.apellido, c.telefono, c.estatus,
            col.nombre AS colonia,
            DATE_FORMAT(c.creado_en,'%d/%m/%Y') AS fecha
     FROM clientes c
     JOIN colonias col ON col.id = c.colonia_id
     ORDER BY c.creado_en DESC
     LIMIT 5"
)->fetchAll();

// ── Clientes por colonia (top 5) ─────
$porColonia = $db->query(
    "SELECT col.nombre, COUNT(c.id) AS total
     FROM clientes c
     JOIN colonias col ON col.id = c.colonia_id
     GROUP BY col.id
     ORDER BY total DESC
     LIMIT 5"
)->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-header">
  <h1>Dashboard</h1>
  <p>Bienvenido al panel de control de AgroGestor GT</p>
</div>

<!-- ── Estadísticas ── -->
<div class="stats-grid">

  <div class="stat-card accent-green">
    <div class="stat-label">Colonias</div>
    <div class="stat-value"><?= $total_colonias ?></div>
    <div class="stat-sub">Registradas en el sistema</div>
  </div>

  <div class="stat-card accent-lime">
    <div class="stat-label">Clientes totales</div>
    <div class="stat-value"><?= $total_clientes ?></div>
    <div class="stat-sub">En todas las colonias</div>
  </div>

  <div class="stat-card accent-gold">
    <div class="stat-label">Clientes activos</div>
    <div class="stat-value"><?= $total_activos ?></div>
    <div class="stat-sub">Con estatus activo</div>
  </div>

  <div class="stat-card accent-rust">
    <div class="stat-label">Pendientes</div>
    <div class="stat-value"><?= $total_pend ?></div>
    <div class="stat-sub">Requieren atención</div>
  </div>

</div>

<div class="two-col-grid">

  <!-- ── Últimos clientes ── -->
  <div class="card">
    <div class="section-title">Últimos 5 clientes registrados</div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Colonia</th>
            <th>Estatus</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recientes)): ?>
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <div class="icon">📭</div>
                  <p>Sin clientes aún. <a href="form_cliente.php">Registra el primero</a></p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php
              $badgeMap = ['activo'=>'badge-green','inactivo'=>'badge-rust','pendiente'=>'badge-gold'];
              $labelMap = ['activo'=>'Activo','inactivo'=>'Inactivo','pendiente'=>'Pendiente'];
            ?>
            <?php foreach ($recientes as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['nombre'] . ' ' . $r['apellido']) ?></td>
                <td class="muted"><?= htmlspecialchars($r['telefono']) ?></td>
                <td><span class="badge badge-green"><?= htmlspecialchars($r['colonia']) ?></span></td>
                <td>
                  <span class="badge <?= $badgeMap[$r['estatus']] ?? '' ?>">
                    <?= $labelMap[$r['estatus']] ?? $r['estatus'] ?>
                  </span>
                </td>
                <td class="muted"><?= $r['fecha'] ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Top colonias ── -->
  <div class="card">
    <div class="section-title">Top colonias con más clientes</div>
    <?php if (empty($porColonia)): ?>
      <div class="empty-state">
        <div class="icon">🏘</div>
        <p>Sin datos aún</p>
      </div>
    <?php else: ?>
      <?php
        $maxVal = $porColonia[0]['total'];
      ?>
      <div class="bar-list">
        <?php foreach ($porColonia as $pc): ?>
          <div class="bar-item">
            <div class="bar-label">
              <span><?= htmlspecialchars($pc['nombre']) ?></span>
              <strong><?= $pc['total'] ?></strong>
            </div>
            <div class="bar-track">
              <div class="bar-fill"
                   style="width: <?= $maxVal > 0 ? round(($pc['total']/$maxVal)*100) : 0 ?>%">
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border)">
      <a href="lista_clientes.php" class="btn btn-primary" style="width:100%;justify-content:center">
        Ver todos los clientes →
      </a>
    </div>
  </div>

</div><!-- /two-col-grid -->

<?php require_once 'includes/footer.php'; ?>
