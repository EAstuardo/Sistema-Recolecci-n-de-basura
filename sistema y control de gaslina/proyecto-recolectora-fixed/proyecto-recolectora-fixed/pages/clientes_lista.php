<?php
/**
 * pages/clientes_lista.php
 * Lista de clientes con filtros y búsqueda
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
$page_title = 'Lista de Clientes';
$page_subtitle = 'Consulta y gestiona todos los clientes';

$db = getDB();

// Filtros
$buscar = trim($_GET['buscar'] ?? '');
$colonia_id = isset($_GET['colonia_id']) ? (int)$_GET['colonia_id'] : 0;
$estatus = $_GET['estatus'] ?? '';

// Construir consulta
$sql = "
    SELECT c.*, col.nombre as colonia_nombre, col.tarifa_mensual
    FROM clientes c
    JOIN colonias col ON col.id = c.colonia_id
    WHERE 1=1
";
$params = [];

if ($buscar) {
    $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.telefono LIKE ? OR c.email LIKE ?)";
    $like = "%$buscar%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($colonia_id > 0) {
    $sql .= " AND c.colonia_id = ?";
    $params[] = $colonia_id;
}

if ($estatus && in_array($estatus, ['activo', 'inactivo', 'pendiente'])) {
    $sql .= " AND c.estatus = ?";
    $params[] = $estatus;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

// Colonias para el filtro
$colonias = $db->query("SELECT id, nombre FROM colonias WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();

// Estadísticas
$totalClientes = $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalActivos = $db->query("SELECT COUNT(*) FROM clientes WHERE estatus = 'activo'")->fetchColumn();

include '../includes/header.php';
?>

<!-- Stats Cards -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($totalClientes); ?></div>
        <div class="stat-label">Total Clientes</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--primary);"><?php echo number_format($totalActivos); ?></div>
        <div class="stat-label">Clientes Activos</div>
    </div>
</div>

<!-- Filtros -->
<div class="filter-bar">
    <form method="GET" action="" style="display: contents; width: 100%;">
        <input type="text" name="buscar" class="form-control" 
               placeholder="🔍 Buscar por nombre, teléfono o correo..." 
               value="<?php echo h($buscar); ?>">
        
        <select name="colonia_id" class="form-control">
            <option value="">Todas las colonias</option>
            <?php foreach ($colonias as $col): ?>
                <option value="<?php echo $col['id']; ?>" <?php echo $colonia_id == $col['id'] ? 'selected' : ''; ?>>
                    <?php echo h($col['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <select name="estatus" class="form-control">
            <option value="">Todos los estatus</option>
            <option value="activo" <?php echo $estatus == 'activo' ? 'selected' : ''; ?>>Activo</option>
            <option value="pendiente" <?php echo $estatus == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
            <option value="inactivo" <?php echo $estatus == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
        </select>
        
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="clientes_lista.php" class="btn btn-secondary">Limpiar</a>
        <a href="exportar_csv.php?<?php echo http_build_query($_GET); ?>" class="btn btn-secondary">📥 Exportar CSV</a>
    </form>
</div>

<!-- Tabla de clientes -->
<div class="card">
    <div class="card-title">
        <span>📋 Clientes Registrados</span>
        <span class="badge badge-info"><?php echo count($clientes); ?> resultados</span>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Colonia</th>
                    <th>Tarifa</th>
                    <th>Estatus</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clientes)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <?php if ($buscar || $colonia_id || $estatus): ?>
                                No se encontraron clientes con los filtros aplicados
                            <?php else: ?>
                                No hay clientes registrados. 
                                <a href="clientes.php">Registra el primer cliente</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clientes as $index => $cliente): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <strong><?php echo h($cliente['nombre'] . ' ' . $cliente['apellido']); ?></strong>
                                <?php if ($cliente['email']): ?>
                                    <br><small class="text-muted"><?php echo h($cliente['email']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo h($cliente['telefono'] ?: '—'); ?></td>
                            <td><?php echo h($cliente['colonia_nombre']); ?></td>
                            <td>Q <?php echo number_format($cliente['tarifa_mensual'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $cliente['estatus'] === 'activo' ? 'success' : 
                                        ($cliente['estatus'] === 'pendiente' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($cliente['estatus']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></td>
                            <td>
                                <a href="pagos.php?cliente_id=<?php echo $cliente['id']; ?>" 
                                   class="btn btn-primary btn-sm" title="Registrar pago">💰</a>
                                <?php if (isAdmin() && $cliente['estatus'] !== 'inactivo'): ?>
                                    <button onclick="desactivarCliente(<?php echo $cliente['id']; ?>)" 
                                            class="btn btn-danger btn-sm" title="Desactivar">🗑️</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function desactivarCliente(id) {
    if (confirm('¿Estás seguro de desactivar este cliente? Podrás reactivarlo más tarde.')) {
        fetch('../api/clientes.php?id=' + id, { method: 'DELETE' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.error, 'error');
                }
            })
            .catch(err => showToast('Error al desactivar', 'error'));
    }
}
</script>

<?php include '../includes/footer.php'; ?>