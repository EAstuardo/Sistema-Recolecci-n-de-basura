<?php
// ══════════════════════════════════════
//  form_colonia.php — Registrar Colonia
// ══════════════════════════════════════
require_once 'includes/db.php';
$titulo_pagina = 'Registrar Colonia';

$db      = getDB();
$errores = [];
$mensaje = null;
$tipo    = '';

// ── Leer mensaje de redirección ───────
if (isset($_GET['msg'])) {
    $msgs = [
        'guardada'  => ['Colonia registrada correctamente.',  'success'],
        'eliminada' => ['Colonia eliminada correctamente.',   'error'],
    ];
    if (isset($msgs[$_GET['msg']])) {
        [$mensaje, $tipo] = $msgs[$_GET['msg']];
    }
}

// ── Eliminar colonia ──────────────────
if (isset($_GET['eliminar']) && ctype_digit($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    try {
        $stmt = $db->prepare("DELETE FROM colonias WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: form_colonia.php?msg=eliminada');
        exit;
    } catch (PDOException $e) {
        $mensaje = '⚠️ No se puede eliminar: la colonia tiene clientes asociados. Elimínalos primero.';
        $tipo    = 'error';
    }
}

// ── Guardar colonia ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['col_nombre']    ?? '');
    $cp          = trim($_POST['col_cp']        ?? '');
    $municipio   = trim($_POST['col_municipio'] ?? '');
    $estado      = trim($_POST['col_estado']    ?? '');
    $descripcion = trim($_POST['col_desc']      ?? '');

    // Validaciones
    if ($nombre === '')
        $errores['col_nombre'] = 'El nombre de la colonia es obligatorio.';
    elseif (mb_strlen($nombre) > 150)
        $errores['col_nombre'] = 'Máximo 150 caracteres.';

    if (!preg_match('/^\d{5}$/', $cp))
        $errores['col_cp'] = 'El código postal debe tener exactamente 5 dígitos.';

    if ($municipio === '')
        $errores['col_municipio'] = 'El municipio es obligatorio.';

    if ($estado === '')
        $errores['col_estado'] = 'El departamento es obligatorio.';

    // Verificar nombre duplicado
    if (empty($errores)) {
        $chk = $db->prepare("SELECT id FROM colonias WHERE LOWER(nombre) = LOWER(?)");
        $chk->execute([$nombre]);
        if ($chk->fetch()) {
            $errores['col_nombre'] = 'Ya existe una colonia con ese nombre.';
        }
    }

    if (empty($errores)) {
        $stmt = $db->prepare(
            "INSERT INTO colonias (nombre, cp, municipio, estado, descripcion)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $cp, $municipio, $estado, $descripcion ?: null]);
        header('Location: form_colonia.php?msg=guardada');
        exit;
    }
}

// ── Listar colonias ───────────────────
$colonias = $db->query(
    "SELECT col.*, COUNT(c.id) AS total_clientes
     FROM colonias col
     LEFT JOIN clientes c ON c.colonia_id = col.id
     GROUP BY col.id
     ORDER BY col.creado_en DESC"
)->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-header">
  <h1>Registrar Colonia</h1>
  <p>Agrega una nueva colonia al sistema</p>
</div>

<?php if ($mensaje): ?>
  <div class="alert alert-<?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<!-- ══ Formulario ══ -->
<div class="card">
  <div class="section-title">📍 Datos de la Colonia</div>

  <form method="POST" action="form_colonia.php" novalidate>

    <div class="form-grid cols-2">

      <!-- Nombre -->
      <div class="field <?= isset($errores['col_nombre']) ? 'has-error' : '' ?>">
        <label for="col_nombre">Nombre de la colonia *</label>
        <input type="text"
               id="col_nombre"
               name="col_nombre"
               placeholder="Ej. Colonia Las Flores"
               maxlength="150"
               value="<?= htmlspecialchars($_POST['col_nombre'] ?? '') ?>"/>
        <?php if (isset($errores['col_nombre'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['col_nombre']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Código Postal -->
      <div class="field <?= isset($errores['col_cp']) ? 'has-error' : '' ?>">
        <label for="col_cp">Código Postal * <small>(5 dígitos)</small></label>
        <input type="text"
               id="col_cp"
               name="col_cp"
               placeholder="Ej. 01001"
               maxlength="5"
               value="<?= htmlspecialchars($_POST['col_cp'] ?? '') ?>"/>
        <?php if (isset($errores['col_cp'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['col_cp']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Municipio -->
      <div class="field <?= isset($errores['col_municipio']) ? 'has-error' : '' ?>">
        <label for="col_municipio">Municipio *</label>
        <input type="text"
               id="col_municipio"
               name="col_municipio"
               placeholder="Ej. Guatemala City"
               maxlength="100"
               value="<?= htmlspecialchars($_POST['col_municipio'] ?? '') ?>"/>
        <?php if (isset($errores['col_municipio'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['col_municipio']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Departamento -->
      <div class="field <?= isset($errores['col_estado']) ? 'has-error' : '' ?>">
        <label for="col_estado">Departamento *</label>
        <select id="col_estado" name="col_estado">
          <option value="">— Selecciona —</option>
          <?php
          $departamentos = [
            'Alta Verapaz','Baja Verapaz','Chimaltenango','Chiquimula',
            'El Progreso','Escuintla','Guatemala','Huehuetenango',
            'Izabal','Jalapa','Jutiapa','Petén','Quetzaltenango',
            'Quiché','Retalhuleu','Sacatepéquez','San Marcos',
            'Santa Rosa','Sololá','Suchitepéquez','Totonicapán','Zacapa'
          ];
          foreach ($departamentos as $dep):
            $sel = (($_POST['col_estado'] ?? '') === $dep) ? 'selected' : '';
          ?>
            <option value="<?= $dep ?>" <?= $sel ?>><?= $dep ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errores['col_estado'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['col_estado']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Descripción -->
      <div class="field" style="grid-column:1/-1">
        <label for="col_desc">Descripción <small>(opcional)</small></label>
        <textarea id="col_desc"
                  name="col_desc"
                  rows="3"
                  placeholder="Notas adicionales sobre la colonia…"><?= htmlspecialchars($_POST['col_desc'] ?? '') ?></textarea>
      </div>

    </div><!-- /form-grid -->

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">✓ Guardar Colonia</button>
      <a href="form_colonia.php" class="btn btn-secondary">✕ Limpiar</a>
    </div>

  </form>
</div>

<!-- ══ Tabla de colonias ══ -->
<div class="card" style="margin-top:24px">
  <div class="section-title">
    🏘 Colonias registradas
    <span class="count-pill"><?= count($colonias) ?></span>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>CP</th>
          <th>Municipio</th>
          <th>Departamento</th>
          <th>Clientes</th>
          <th>Registrada</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($colonias)): ?>
          <tr>
            <td colspan="8">
              <div class="empty-state">
                <div class="icon">🏘</div>
                <p>Sin colonias registradas todavía.</p>
              </div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($colonias as $i => $c): ?>
            <tr>
              <td class="muted"><?= $i + 1 ?></td>
              <td><strong><?= htmlspecialchars($c['nombre']) ?></strong>
                <?php if ($c['descripcion']): ?>
                  <br><small class="muted"><?= htmlspecialchars(mb_substr($c['descripcion'],0,50)) ?>…</small>
                <?php endif; ?>
              </td>
              <td class="muted"><?= htmlspecialchars($c['cp']) ?></td>
              <td><?= htmlspecialchars($c['municipio']) ?></td>
              <td><?= htmlspecialchars($c['estado']) ?></td>
              <td>
                <span class="badge badge-green"><?= $c['total_clientes'] ?></span>
              </td>
              <td class="muted"><?= date('d/m/Y', strtotime($c['creado_en'])) ?></td>
              <td>
                <a href="form_colonia.php?eliminar=<?= $c['id'] ?>"
                   class="btn btn-danger"
                   onclick="return confirm('¿Eliminar la colonia «<?= htmlspecialchars(addslashes($c['nombre'])) ?>»?\nEsta acción no se puede deshacer.')">
                  Eliminar
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
