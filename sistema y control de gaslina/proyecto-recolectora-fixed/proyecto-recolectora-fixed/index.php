<?php
/**
 * index.php
 * Dashboard principal del sistema
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$page_title = 'Dashboard';
$page_subtitle = 'Resumen general del sistema';

try {
    $db = getDB();
    
    // Estadísticas
    $totalColonias = (int) $db->query("SELECT COUNT(*) FROM colonias WHERE activo = 1")->fetchColumn();
    $totalClientes = (int) $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $totalActivos = (int) $db->query("SELECT COUNT(*) FROM clientes WHERE estatus = 'activo'")->fetchColumn();
    
    // Recaudación del mes actual
    $mesActual = date('n');
    $anioActual = date('Y');
    $stmt = $db->prepare("SELECT SUM(monto) as total FROM pagos WHERE mes = ? AND anio = ?");
    $stmt->execute([$mesActual, $anioActual]);
    $recaudacionMes = $stmt->fetch()['total'] ?? 0;
    
    // Últimos clientes
    $ultimosClientes = $db->query("
        SELECT c.id, c.nombre, c.apellido, c.telefono, c.estatus, col.nombre as colonia
        FROM clientes c
        JOIN colonias col ON col.id = c.colonia_id
        ORDER BY c.created_at DESC
        LIMIT 5
    ")->fetchAll();
    
    // Pagos recientes
    $pagosRecientes = $db->query("
        SELECT p.*, c.nombre, c.apellido, col.nombre as colonia, u.nombre as cobrador
        FROM pagos p
        JOIN clientes c ON c.id = p.cliente_id
        JOIN colonias col ON col.id = c.colonia_id
        JOIN usuarios u ON u.id = p.usuario_id
        ORDER BY p.fecha_pago DESC
        LIMIT 10
    ")->fetchAll();
    
} catch (PDOException $e) {
    error_log('Error en dashboard: ' . $e->getMessage());
    $error = 'Error al cargar los datos';
}

include 'includes/header.php';
?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo h($error); ?></div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-icon">🏘️</span>
        </div>
        <div class="stat-value"><?php echo number_format($totalColonias); ?></div>
        <div class="stat-label">Colonias Activas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-icon">👥</span>
        </div>
        <div class="stat-value"><?php echo number_format($totalClientes); ?></div>
        <div class="stat-label">Total Clientes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-icon">✅</span>
        </div>
        <div class="stat-value"><?php echo number_format($totalActivos); ?></div>
        <div class="stat-label">Clientes Activos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-icon">💰</span>
        </div>
        <div class="stat-value">Q <?php echo number_format($recaudacionMes, 2); ?></div>
        <div class="stat-label">Recaudación del Mes</div>
    </div>
</div>

<!-- Últimos Clientes -->
<div class="card">
    <div class="card-title">
        <span>📋 Últimos Clientes Registrados</span>
        <a href="pages/clientes_lista.php" class="btn btn-secondary btn-sm">Ver todos →</a>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Colonia</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimosClientes)): ?>
                    <tr>
                        <td colspan="4" class="empty-state">
                            No hay clientes registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ultimosClientes as $cliente): ?>
                        <tr>
                            <td><?php echo h($cliente['nombre'] . ' ' . $cliente['apellido']); ?></td>
                            <td><?php echo h($cliente['telefono'] ?: '—'); ?></td>
                            <td><?php echo h($cliente['colonia']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $cliente['estatus'] === 'activo' ? 'success' : ($cliente['estatus'] === 'pendiente' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($cliente['estatus']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagos Recientes -->
<div class="card">
    <div class="card-title">
        <span>🧾 Pagos Recientes</span>
        <a href="pages/recibos.php" class="btn btn-secondary btn-sm">Ver todos →</a>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Colonia</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Cobrador</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagosRecientes)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            No hay pagos registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagosRecientes as $pago): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                            <td><?php echo h($pago['nombre'] . ' ' . $pago['apellido']); ?></td>
                            <td><?php echo h($pago['colonia']); ?></td>
                            <td><strong>Q <?php echo number_format($pago['monto'], 2); ?></strong></td>
                            <td><?php echo $pago['metodo_pago']; ?></td>
                            <td><?php echo h($pago['cobrador']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>