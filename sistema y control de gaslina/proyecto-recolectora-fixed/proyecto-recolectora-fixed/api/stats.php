<?php
/**
 * api/stats.php
 * API para obtener estadísticas del dashboard
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
header('Content-Type: application/json');

$db = getDB();

try {
    // Estadísticas básicas
    $totalColonias = (int) $db->query("SELECT COUNT(*) FROM colonias WHERE activo = 1")->fetchColumn();
    $totalClientes = (int) $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $totalActivos = (int) $db->query("SELECT COUNT(*) FROM clientes WHERE estatus = 'activo'")->fetchColumn();
    $totalPendientes = (int) $db->query("SELECT COUNT(*) FROM clientes WHERE estatus = 'pendiente'")->fetchColumn();
    
    // Recaudación del mes actual
    $mesActual = date('n');
    $anioActual = date('Y');
    $stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE mes = ? AND anio = ?");
    $stmt->execute([$mesActual, $anioActual]);
    $recaudacionMes = (float) $stmt->fetch()['total'];
    
    // Recaudación del mes anterior (para comparación)
    $mesAnterior = $mesActual == 1 ? 12 : $mesActual - 1;
    $anioAnterior = $mesActual == 1 ? $anioActual - 1 : $anioActual;
    $stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE mes = ? AND anio = ?");
    $stmt->execute([$mesAnterior, $anioAnterior]);
    $recaudacionMesAnterior = (float) $stmt->fetch()['total'];
    
    // Porcentaje de cambio
    $porcentajeCambio = 0;
    if ($recaudacionMesAnterior > 0) {
        $porcentajeCambio = round(($recaudacionMes - $recaudacionMesAnterior) / $recaudacionMesAnterior * 100, 1);
    }
    
    // Pagos del día de hoy
    $hoy = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(monto), 0) as monto FROM pagos WHERE fecha_pago = ?");
    $stmt->execute([$hoy]);
    $pagosHoy = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'total_colonias' => $totalColonias,
        'total_clientes' => $totalClientes,
        'clientes_activos' => $totalActivos,
        'clientes_pendientes' => $totalPendientes,
        'recaudacion_mes' => $recaudacionMes,
        'recaudacion_mes_anterior' => $recaudacionMesAnterior,
        'porcentaje_cambio' => $porcentajeCambio,
        'pagos_hoy' => $pagosHoy['total'],
        'monto_hoy' => (float) $pagosHoy['monto']
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
}