<?php
/**
 * pages/recibos.php
 * Listado de recibos emitidos con filtros
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
$page_title = 'Recibos';
$page_subtitle = 'Historial de recibos emitidos';

$db = getDB();

// Filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
$cliente_id = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;

$sql = "
    SELECT r.*, p.monto, p.fecha_pago, p.metodo_pago,
           c.id as cliente_id, c.nombre, c.apellido, col.nombre as colonia,
           u.nombre as cobrador
    FROM recibos r
    JOIN pagos p ON p.id = r.pago_id
    JOIN clientes c ON c.id = p.cliente_id
    JOIN colonias col ON col.id = c.colonia_id
    JOIN usuarios u ON u.id = p.usuario_id
    WHERE DATE(p.fecha_pago) BETWEEN ? AND ?
";
$params = [$fecha_inicio, $fecha_fin];

if ($cliente_id > 0) {
    $sql .= " AND c.id = ?";
    $params[] = $cliente_id;
}

$sql .= " ORDER BY r.fecha_emision DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$recibos = $stmt->fetchAll();

// Obtener clientes para el filtro
$clientes = $db->query("SELECT id, nombre, apellido FROM clientes WHERE estatus = 'activo' ORDER BY nombre ASC")->fetchAll();

include '../includes/header.php';
?>

<!-- Filtros -->
<div class="filter-bar">
    <form method="GET" action="" style="display: contents;">
        <input type="date" name="fecha_inicio" class="form-control" value="<?php echo h($fecha_inicio); ?>">
        <input type="date" name="fecha_fin" class="form-control" value="<?php echo h($fecha_fin); ?>">
        
        <select name="cliente_id" class="form-control">
            <option value="">Todos los clientes</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo $cliente['id']; ?>" <?php echo $cliente_id == $cliente['id'] ? 'selected' : ''; ?>>
                    <?php echo h($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="recibos.php" class="btn btn-secondary">Limpiar</a>
    </form>
</div>

<div class="card">
    <div class="card-title">
        <span>🧾 Recibos Emitidos</span>
        <span class="badge badge-info"><?php echo count($recibos); ?> recibos</span>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>N° Recibo</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Colonia</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Cobrador</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recibos)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            No hay recibos en el período seleccionado
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recibos as $recibo): ?>
                        <tr>
                            <td><strong><?php echo h($recibo['numero_recibo']); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($recibo['fecha_pago'])); ?></td>
                            <td>
                                <?php echo h($recibo['nombre'] . ' ' . $recibo['apellido']); ?>
                                <br><small class="text-muted">ID: <?php echo $recibo['cliente_id']; ?></small>
                            </td>
                            <td><?php echo h($recibo['colonia']); ?></td>
                            <td><strong>Q <?php echo number_format($recibo['monto'], 2); ?></strong></td>
                            <td><?php echo $recibo['metodo_pago']; ?></td>
                            <td><?php echo h($recibo['cobrador']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>