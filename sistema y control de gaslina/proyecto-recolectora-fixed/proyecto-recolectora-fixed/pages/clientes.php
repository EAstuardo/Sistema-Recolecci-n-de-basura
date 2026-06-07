<?php
/**
 * pages/clientes.php
 * Formulario para registrar un nuevo cliente
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
$page_title = 'Registrar Cliente';
$page_subtitle = 'Agrega un nuevo cliente al sistema';

$db = getDB();
$mensaje = null;
$tipo_mensaje = '';

// Obtener colonias para el select
$colonias = $db->query("SELECT id, nombre, tarifa_mensual FROM colonias WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $referencia = trim($_POST['referencia'] ?? '');
    $colonia_id = (int)($_POST['colonia_id'] ?? 0);
    $estatus = $_POST['estatus'] ?? 'activo';
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre)) $errores[] = 'El nombre es obligatorio';
    if (empty($apellido)) $errores[] = 'El apellido es obligatorio';
    if ($colonia_id <= 0) $errores[] = 'Debes seleccionar una colonia';
    if (!empty($telefono) && !preg_match('/^\d{8}$/', $telefono)) {
        $errores[] = 'El teléfono debe tener 8 dígitos';
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido';
    }
    
    if (empty($errores)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO clientes (nombre, apellido, telefono, email, direccion, referencia, colonia_id, estatus)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $nombre, $apellido, $telefono ?: null, $email ?: null,
                $direccion ?: null, $referencia ?: null, $colonia_id, $estatus
            ]);
            
            $mensaje = 'Cliente registrado correctamente';
            $tipo_mensaje = 'success';
            
            // Limpiar formulario
            $_POST = [];
            
        } catch (PDOException $e) {
            $mensaje = 'Error al guardar: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = implode(', ', $errores);
        $tipo_mensaje = 'error';
    }
}

include '../includes/header.php';
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo h($mensaje); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">📝 Datos del Cliente</div>
    
    <?php if (empty($colonias)): ?>
        <div class="alert alert-warning">
            ⚠️ No hay colonias registradas. 
            <a href="colonias.php">Registra una colonia primero</a>.
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre(s) <span class="required">*</span></label>
                <input type="text" id="nombre" name="nombre" class="form-control" required 
                       value="<?php echo h($_POST['nombre'] ?? ''); ?>" placeholder="Ej. María">
            </div>
            
            <div class="form-group">
                <label for="apellido">Apellidos <span class="required">*</span></label>
                <input type="text" id="apellido" name="apellido" class="form-control" required 
                       value="<?php echo h($_POST['apellido'] ?? ''); ?>" placeholder="Ej. García López">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="telefono">Teléfono <small>(8 dígitos)</small></label>
                <input type="tel" id="telefono" name="telefono" class="form-control" 
                       value="<?php echo h($_POST['telefono'] ?? ''); ?>" placeholder="Ej. 42459401" maxlength="8">
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo h($_POST['email'] ?? ''); ?>" placeholder="ejemplo@correo.com">
            </div>
        </div>
        
        <div class="form-group">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" class="form-control" 
                   value="<?php echo h($_POST['direccion'] ?? ''); ?>" placeholder="Calle y número">
        </div>
        
        <div class="form-group">
            <label for="referencia">Referencia (opcional)</label>
            <input type="text" id="referencia" name="referencia" class="form-control" 
                   value="<?php echo h($_POST['referencia'] ?? ''); ?>" placeholder="Casa azul, frente a la iglesia">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="colonia_id">Colonia <span class="required">*</span></label>
                <select id="colonia_id" name="colonia_id" class="form-control" required>
                    <option value="">— Selecciona una colonia —</option>
                    <?php foreach ($colonias as $colonia): ?>
                        <option value="<?php echo $colonia['id']; ?>" 
                            <?php echo (($_POST['colonia_id'] ?? '') == $colonia['id']) ? 'selected' : ''; ?>>
                            <?php echo h($colonia['nombre']); ?> (Q <?php echo number_format($colonia['tarifa_mensual'], 2); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="estatus">Estatus</label>
                <select id="estatus" name="estatus" class="form-control">
                    <option value="activo" <?php echo (($_POST['estatus'] ?? '') == 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="pendiente" <?php echo (($_POST['estatus'] ?? '') == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="inactivo" <?php echo (($_POST['estatus'] ?? '') == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" <?php echo empty($colonias) ? 'disabled' : ''; ?>>
                ✓ Guardar Cliente
            </button>
            <a href="clientes_lista.php" class="btn btn-secondary">Ver Lista de Clientes</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>