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
      <?php include __DIR__ . '/lista_alertas.php'; ?>
    </div>
  </div>
</div>


