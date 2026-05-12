<?php
// ══════════════════════════════════════
//  exportar_csv.php — Descarga CSV
// ══════════════════════════════════════
require_once 'includes/db.php';
$db = getDB();

$buscar     = trim($_GET['buscar']   ?? '');
$filColonia = (int)($_GET['colonia'] ?? 0);
$filEstatus = trim($_GET['estatus']  ?? '');

$where  = [];
$params = [];

if ($buscar !== '') {
    $where[]  = "(c.nombre LIKE ? OR c.apellido LIKE ? OR c.telefono LIKE ? OR c.email LIKE ?)";
    $like     = "%$buscar%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($filColonia > 0) {
    $where[]  = "c.colonia_id = ?";
    $params[] = $filColonia;
}
if (in_array($filEstatus, ['activo','inactivo','pendiente'], true)) {
    $where[]  = "c.estatus = ?";
    $params[] = $filEstatus;
}

$sql = "SELECT c.nombre, c.apellido, c.telefono,
               COALESCE(c.email,'') AS email,
               c.calle,
               COALESCE(c.referencia,'') AS referencia,
               col.nombre AS colonia,
               col.municipio,
               col.estado AS departamento,
               c.estatus,
               DATE_FORMAT(c.creado_en,'%d/%m/%Y') AS fecha
        FROM clientes c
        JOIN colonias col ON col.id = c.colonia_id"
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY c.creado_en DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$filas = $stmt->fetchAll();

// ── Headers para descarga ─────────────
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="clientes_' . date('Ymd_His') . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');

$out = fopen('php://output', 'w');

// BOM UTF-8 para que Excel no tenga problemas con tildes
fwrite($out, "\xEF\xBB\xBF");

// Cabecera
fputcsv($out, [
    'Nombre', 'Apellido', 'Teléfono', 'Correo',
    'Calle', 'Referencia', 'Colonia', 'Municipio',
    'Departamento', 'Estatus', 'Fecha de registro'
]);

foreach ($filas as $f) {
    fputcsv($out, array_values($f));
}

fclose($out);
exit;
?>
