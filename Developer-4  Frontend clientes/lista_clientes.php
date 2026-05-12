<?php
// ══════════════════════════════════════
//  lista_clientes.php — Lista + Filtros
// ══════════════════════════════════════
require_once 'includes/db.php';
$titulo_pagina = 'Lista de Clientes';

$db = getDB();

// ── Eliminar cliente ──────────────────
if (isset($_GET['eliminar']) && ctype_digit($_GET['eliminar'])) {
    $stmt = $db->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->execute([(int)$_GET['eliminar']]);
    header('Location: lista_clientes.php?msg=eliminado');
    exit;
}

$mensaje = null;
$tipo    = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
    $mensaje = 'Cliente eliminado correctamente.';
    $tipo    = 'error';
}

// ── Filtros ───────────────────────────
$buscar     = trim($_GET['buscar']   ?? '');
$filColonia = (int)($_GET['colonia'] ?? 0);
$filEstatus = trim($_GET['estatus']  ?? '');

// ── Construir query ───────────────────
$where  = [];
$params = [];

if ($buscar !== '') {
    $where[]  = "(c.nombre LIKE ? OR c.apellido LIKE ? OR c.telefono LIKE ? OR c.email LIKE ?)";
    $like     = "%$buscar%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($filColonia > 0) {
    $where[]  = "c.colonia_id = ?";
    $params[] = $filColonia;
}
if (in_array($filEstatus, ['activo','inactivo','pendiente'], true)) {
    $where[]  = "c.estatus = ?";
    $params[] = $filEstatus;
}

$sql = "SELECT c.id, c.nombre, c.apellido, c.telefono, c.email,
               c.calle, c.referencia, c.estatus,
               col.nombre AS colonia_nombre,
               DATE_FORMAT(c.creado_en,'%d/%m/%Y') AS fecha
        FROM clientes c
        JOIN colonias col ON col.id = c.colonia_id"
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY c.creado_en DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

// ── Colonias para el filtro ───────────
$colonias = $db->query(
    "SELECT id, nombre FROM colonias ORDER BY nombre ASC"
)->fetchAll();

// ── Conteo total sin filtros ──────────
$total_sin_filtro = (int) $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();

require_once 'includes/header.php';
?>

<div class="page-header">
  <h1>Lista de Clientes</h1>
  <p>Consulta, filtra y gestiona todos los clientes registrados</p>
</div>

<?php if ($mensaje): ?>
  <div class="alert alert-<?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<!-- ── Barra de filtros ── -->
<form method="GET" action="lista_clientes.php" class="filter-bar">

  <div class="search-wrap">
    <span class="ico">🔍</span>
    <input type="text"
           name="buscar"
           placeholder="Buscar por nombre, teléfono o correo…"
           value="<?= htmlspecialchars($buscar) ?>"/>
  </div>

  <select name="colonia">
    <option value="">Todas las colonias</option>
    <?php foreach ($colonias as $col): ?>
      <option value="<?= $col['id'] ?>"
        <?= ($filColonia === $col['id']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($col['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <select name="estatus">
    <option value="">Todos los estatus</option>
    <?php foreach (['activo'=>'Activo','inactivo'=>'Inactivo','pendiente'=>'Pendiente'] as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($filEstatus === $v) ? 'selected' : '' ?>>
        <?= $l ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
  <a href="lista_clientes.php" class="btn btn-secondary">✕ Limpiar</a>
  <a href="exportar_csv.php?buscar=<?= urlencode($buscar) ?>&colonia=<?= $filColonia ?>&estatus=<?= urlencode($filEstatus) ?>"
     class="btn btn-secondary">⬇ CSV</a>
</form>

<!-- ── Tabla ── -->
<div class="card">
  <div class="section-title">
    📋 Clientes
    <span class="count-pill">
      <?= count($clientes) ?>
      <?php if (count($clientes) !== $total_sin_filtro): ?>
        / <?= $total_sin_filtro ?>
      <?php endif; ?>
    </span>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre completo</th>
          <th>Teléfono</th>
          <th>Correo</th>
          <th>Dirección</th>
          <th>Colonia</th>
          <th>Estatus</th>
          <th>Fecha</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($clientes)): ?>
          <tr>
            <td colspan="9">
              <div class="empty-state">
                <div class="icon"><?= ($buscar || $filColonia || $filEstatus) ? '🔍' : '👤' ?></div>
                <p>
                  <?= ($buscar || $filColonia || $filEstatus)
                      ? 'Sin resultados para este filtro. <a href="lista_clientes.php">Ver todos</a>'
                      : 'Sin clientes registrados. <a href="form_cliente.php">Registra el primero</a>' ?>
                </p>
              </div>
            </td>
          </tr>
        <?php else: ?>
          <?php
            $badgeMap = ['activo'=>'badge-green','inactivo'=>'badge-rust','pendiente'=>'badge-gold'];
            $labelMap = ['activo'=>'Activo','inactivo'=>'Inactivo','pendiente'=>'Pendiente'];
          ?>
          <?php foreach ($clientes as $i => $c): ?>
            <tr>
              <td class="muted"><?= $i + 1 ?></td>
              <td>
                <strong><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></strong>
              </td>
              <td class="muted"><?= htmlspecialchars($c['telefono']) ?></td>
              <td class="muted"><?= $c['email'] ? htmlspecialchars($c['email']) : '—' ?></td>
              <td>
                <?= htmlspecialchars($c['calle']) ?>
                <?php if ($c['referencia']): ?>
                  <br><small class="muted"><?= htmlspecialchars($c['referencia']) ?></small>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-green">
                  <?= htmlspecialchars($c['colonia_nombre']) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $badgeMap[$c['estatus']] ?? '' ?>">
                  <?= $labelMap[$c['estatus']] ?? htmlspecialchars($c['estatus']) ?>
                </span>
              </td>
              <td class="muted"><?= $c['fecha'] ?></td>
              <td>
                <a href="lista_clientes.php?eliminar=<?= $c['id'] ?>"
                   class="btn btn-danger"
                   onclick="return confirm('¿Eliminar a <?= htmlspecialchars(addslashes($c['nombre'] . ' ' . $c['apellido'])) ?>?\nEsta acción no se puede deshacer.')">
                  ✕
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
