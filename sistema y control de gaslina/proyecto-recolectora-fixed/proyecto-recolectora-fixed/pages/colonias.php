<?php
/**
 * pages/colonias.php
 * Gestión de colonias (listado + formulario)
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
$page_title = 'Colonias';
$page_subtitle = 'Gestiona las colonias y sus tarifas';

$db = getDB();
$mensaje = null;
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tarifa_mensual = (float)($_POST['tarifa_mensual'] ?? 0);
    
    if (empty($nombre)) {
        $mensaje = 'El nombre de la colonia es obligatorio';
        $tipo_mensaje = 'error';
    } elseif ($tarifa_mensual <= 0) {
        $mensaje = 'La tarifa mensual debe ser mayor a 0';
        $tipo_mensaje = 'error';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO colonias (nombre, descripcion, tarifa_mensual) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $descripcion ?: null, $tarifa_mensual]);
            $mensaje = 'Colonia registrada correctamente';
            $tipo_mensaje = 'success';
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $mensaje = 'Ya existe una colonia con ese nombre';
            } else {
                $mensaje = 'Error al guardar: ' . $e->getMessage();
            }
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener colonias
$colonias = $db->query("
    SELECT c.*, COUNT(cl.id) as total_clientes 
    FROM colonias c
    LEFT JOIN clientes cl ON cl.colonia_id = c.id
    WHERE c.activo = 1
    GROUP BY c.id
    ORDER BY c.nombre ASC
")->fetchAll();

include '../includes/header.php';
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo h($mensaje); ?></div>
<?php endif; ?>

<!-- Formulario para nueva colonia -->
<div class="card">
    <div class="card-title">➕ Agregar Nueva Colonia</div>
    
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre de la Colonia <span class="required">*</span></label>
                <input type="text" id="nombre" name="nombre" class="form-control" required placeholder="Ej. Colonia Las Flores">
            </div>
            
            <div class="form-group">
                <label for="tarifa_mensual">Tarifa Mensual (Q) <span class="required">*</span></label>
                <input type="number" id="tarifa_mensual" name="tarifa_mensual" class="form-control" required step="0.01" min="0" placeholder="0.00">
            </div>
        </div>
        
        <div class="form-group">
            <label for="descripcion">Descripción (opcional)</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="2" placeholder="Información adicional sobre la colonia..."></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">✓ Guardar Colonia</button>
        </div>
    </form>
</div>

<!-- Listado de colonias -->
<div class="card">
    <div class="card-title">
        <span>🏘️ Colonias Registradas</span>
        <span class="badge badge-info">Total: <?php echo count($colonias); ?></span>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tarifa Mensual</th>
                    <th>Clientes</th>
                    <th>Descripción</th>
                    <th>Registrada</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($colonias)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            No hay colonias registradas
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($colonias as $colonia): ?>
                        <tr>
                            <td><strong><?php echo h($colonia['nombre']); ?></strong></td>
                            <td><strong>Q <?php echo number_format($colonia['tarifa_mensual'], 2); ?></strong></td>
                            <td>
                                <span class="badge badge-<?php echo $colonia['total_clientes'] > 0 ? 'success' : 'secondary'; ?>">
                                    <?php echo $colonia['total_clientes']; ?> clientes
                                </span>
                            </td>
                            <td><?php echo h($colonia['descripcion'] ?: '—'); ?></td>
                            <td class="text-muted"><?php echo date('d/m/Y', strtotime($colonia['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>