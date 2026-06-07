<?php
/**
 * pages/exportar_csv.php
 * Exportación de clientes a CSV
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

$db = getDB();

// Obtener mismos filtros que en lista_clientes
$buscar = trim($_GET['buscar'] ?? '');
$colonia_id = isset($_GET['colonia_id']) ? (int)$_GET['colonia_id'] : 0;
$estatus = $_GET['estatus'] ?? '';

$sql = "
    SELECT c.nombre, c.apellido, c.telefono, c.email, c.direccion,
           col.nombre as colonia, col.tarifa_mensual, c.estatus,
           DATE_FORMAT(c.created_at, '%d/%m/%Y') as fecha_registro
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

$sql .= " ORDER BY c.nombre ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

// Headers para descarga
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="clientes_' . date('Ymd_His') . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

// Cabeceras
fputcsv($output, [
    'Nombre', 'Apellido', 'Teléfono', 'Correo', 'Dirección',
    'Colonia', 'Tarifa Mensual', 'Estatus', 'Fecha Registro'
]);

// Datos
foreach ($clientes as $cliente) {
    fputcsv($output, [
        $cliente['nombre'],
        $cliente['apellido'],
        $cliente['telefono'] ?? '',
        $cliente['email'] ?? '',
        $cliente['direccion'] ?? '',
        $cliente['colonia'],
        $cliente['tarifa_mensual'],
        $cliente['estatus'],
        $cliente['fecha_registro']
    ]);
}

fclose($output);
exit;