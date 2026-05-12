<?php
// ══════════════════════════════════════
//  form_cliente.php — Registrar Cliente
// ══════════════════════════════════════
require_once 'includes/db.php';
$titulo_pagina = 'Registrar Cliente';

$db      = getDB();
$errores = [];
$mensaje = null;
$tipo    = '';

// ── Mensaje de éxito tras redireccion ─
if (isset($_GET['msg']) && $_GET['msg'] === 'guardado') {
    $mensaje = '✓ Cliente registrado correctamente.';
    $tipo    = 'success';
}

// ── Guardar cliente ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = trim($_POST['cli_nombre']     ?? '');
    $apellido   = trim($_POST['cli_apellido']   ?? '');
    $telefono   = trim($_POST['cli_telefono']   ?? '');
    $email      = trim($_POST['cli_email']      ?? '');
    $calle      = trim($_POST['cli_calle']      ?? '');
    $referencia = trim($_POST['cli_referencia'] ?? '');
    $colonia_id = (int)($_POST['cli_colonia']   ?? 0);
    $estatus    = trim($_POST['cli_estatus']    ?? '');

    // ── Validaciones ─────────────────
    if ($nombre === '')
        $errores['cli_nombre'] = 'El nombre es obligatorio.';

    if ($apellido === '')
        $errores['cli_apellido'] = 'Los apellidos son obligatorios.';

    if (!preg_match('/^\d{8}$/', $telefono))
        $errores['cli_telefono'] = 'El teléfono debe tener exactamente 8 dígitos (Guatemala).';

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errores['cli_email'] = 'Ingresa un correo electrónico válido.';

    if ($calle === '')
        $errores['cli_calle'] = 'La calle y número son obligatorios.';

    if ($colonia_id === 0)
        $errores['cli_colonia'] = 'Selecciona una colonia.';

    if (!in_array($estatus, ['activo','inactivo','pendiente'], true))
        $errores['cli_estatus'] = 'Selecciona un estatus válido.';

    // ── Verificar que la colonia exista ─
    if ($colonia_id > 0) {
        $chk = $db->prepare("SELECT id FROM colonias WHERE id = ?");
        $chk->execute([$colonia_id]);
        if (!$chk->fetch())
            $errores['cli_colonia'] = 'La colonia seleccionada no existe.';
    }

    // ── Insertar si no hay errores ────
    if (empty($errores)) {
        $stmt = $db->prepare(
            "INSERT INTO clientes
               (nombre, apellido, telefono, email, calle, referencia, colonia_id, estatus)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $nombre,
            $apellido,
            $telefono,
            $email      !== '' ? $email      : null,
            $calle,
            $referencia !== '' ? $referencia : null,
            $colonia_id,
            $estatus,
        ]);
        header('Location: form_cliente.php?msg=guardado');
        exit;
    }
}

// ── Cargar colonias para el select ───
$colonias = $db->query(
    "SELECT id, nombre FROM colonias ORDER BY nombre ASC"
)->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-header">
  <h1>Registrar Cliente</h1>
  <p>Agrega un nuevo cliente y asígnalo a su colonia</p>
</div>

<?php if ($mensaje): ?>
  <div class="alert alert-<?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<?php if (empty($colonias)): ?>
  <div class="alert alert-error">
    ⚠️ No hay colonias registradas.
    <a href="form_colonia.php">Registra una colonia primero</a> antes de agregar clientes.
  </div>
<?php endif; ?>

<div class="card">

  <!-- ── Datos Personales ── -->
  <div class="section-title">👤 Datos Personales</div>

  <form method="POST" action="form_cliente.php" novalidate>

    <div class="form-grid cols-2">

      <!-- Nombre(s) -->
      <div class="field <?= isset($errores['cli_nombre']) ? 'has-error' : '' ?>">
        <label for="cli_nombre">Nombre(s) *</label>
        <input type="text"
               id="cli_nombre"
               name="cli_nombre"
               placeholder="Ej. María"
               maxlength="100"
               value="<?= htmlspecialchars($_POST['cli_nombre'] ?? '') ?>"/>
        <?php if (isset($errores['cli_nombre'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['cli_nombre']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Apellidos -->
      <div class="field <?= isset($errores['cli_apellido']) ? 'has-error' : '' ?>">
        <label for="cli_apellido">Apellidos *</label>
        <input type="text"
               id="cli_apellido"
               name="cli_apellido"
               placeholder="Ej. García López"
               maxlength="100"
               value="<?= htmlspecialchars($_POST['cli_apellido'] ?? '') ?>"/>
        <?php if (isset($errores['cli_apellido'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['cli_apellido']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Teléfono — 8 dígitos Guatemala -->
      <div class="field <?= isset($errores['cli_telefono']) ? 'has-error' : '' ?>">
        <label for="cli_telefono">
          Teléfono * <small>📱 8 dígitos — Guatemala</small>
        </label>
        <input type="tel"
               id="cli_telefono"
               name="cli_telefono"
               placeholder="Ej. 42459401"
               maxlength="8"
               value="<?= htmlspecialchars($_POST['cli_telefono'] ?? '') ?>"/>
        <?php if (isset($errores['cli_telefono'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['cli_telefono']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Correo electrónico -->
      <div class="field <?= isset($errores['cli_email']) ? 'has-error' : '' ?>">
        <label for="cli_email">Correo electrónico <small>(opcional)</small></label>
        <input type="email"
               id="cli_email"
               name="cli_email"
               placeholder="ejemplo@correo.com"
               maxlength="150"
               value="<?= htmlspecialchars($_POST['cli_email'] ?? '') ?>"/>
        <?php if (isset($errores['cli_email'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['cli_email']) ?></span>
        <?php endif; ?>
      </div>

    </div><!-- /datos personales -->

    <!-- ── Dirección ── -->
    <div class="section-title" style="margin-top:28px">📍 Dirección</div>

    <div class="form-grid cols-2">

      <!-- Calle -->
      <div class="field <?= isset($errores['cli_calle']) ? 'has-error' : '' ?>">
        <label for="cli_calle">Calle y número *</label>
        <input type="text"
               id="cli_calle"
               name="cli_calle"
               placeholder="Ej. 5a Av. 10-25 Zona 1"
               maxlength="200"
               value="<?= htmlspecialchars($_POST['cli_calle'] ?? '') ?>"/>
        <?php if (isset($errores['cli_calle'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['cli_calle']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Colonia — desde la BD -->
      <div class="field <?= isset($errores['cli_colonia']) ? 'has-error' : '' ?>">
        <label for="cli_colonia">Colonia *</label>
        <select id="cli_colonia" name="cli_colonia"
                <?= empty($colonias) ? 'disabled' : '' ?>>
          <option value="">— Selecciona una colonia —</option>
          <?php foreach ($colonias as $col): ?>
            <option value="<?= $col['id'] ?>"
              <?= (isset($_POST['cli_colonia']) && (int)$_POST['cli_colonia'] === $col['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($col['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errores['cli_colonia'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['cli_colonia']) ?></span>
        <?php elseif (empty($colonias)): ?>
          <span class="error-msg" style="display:block">
            Primero <a href="form_colonia.php">registra colonias</a>.
          </span>
        <?php endif; ?>
      </div>

      <!-- Referencia -->
      <div class="field">
        <label for="cli_referencia">Referencia <small>(opcional)</small></label>
        <input type="text"
               id="cli_referencia"
               name="cli_referencia"
               placeholder="Ej. Casa azul, frente a la iglesia"
               maxlength="200"
               value="<?= htmlspecialchars($_POST['cli_referencia'] ?? '') ?>"/>
      </div>

      <!-- Estatus -->
      <div class="field <?= isset($errores['cli_estatus']) ? 'has-error' : '' ?>">
        <label for="cli_estatus">Estatus *</label>
        <select id="cli_estatus" name="cli_estatus">
          <option value="">— Selecciona —</option>
          <?php foreach (['activo' => 'Activo', 'inactivo' => 'Inactivo', 'pendiente' => 'Pendiente'] as $v => $l): ?>
            <option value="<?= $v ?>"
              <?= (($_POST['cli_estatus'] ?? '') === $v) ? 'selected' : '' ?>>
              <?= $l ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errores['cli_estatus'])): ?>
          <span class="error-msg"><?= htmlspecialchars($errores['cli_estatus']) ?></span>
        <?php endif; ?>
      </div>

    </div><!-- /dirección -->

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"
              <?= empty($colonias) ? 'disabled title="Registra una colonia primero"' : '' ?>>
        ✓ Guardar Cliente
      </button>
      <a href="form_cliente.php" class="btn btn-secondary">✕ Limpiar</a>
    </div>

  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
