<?php
// api/reportes/ganancia_diaria.php — GET ganancia real del día (Wendy + Natalia)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/db.php';

$fecha = !empty($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// 1. Ingresos del día (tabla pagos)
$stmtP = $pdo->prepare(
    "SELECT COALESCE(SUM(monto), 0) AS ingresos_dia FROM pagos WHERE fecha_pago = ?"
);
$stmtP->execute([$fecha]);
$ingresos = (float) $stmtP->fetchColumn();

// 2. Gasto en combustible ese día
$stmtC = $pdo->prepare(
    "SELECT COALESCE(SUM(costo_total), 0) AS gasto_combustible FROM combustible WHERE fecha = ?"
);
$stmtC->execute([$fecha]);
$gasto = (float) $stmtC->fetchColumn();

// 3. Alertas del día
$stmtA = $pdo->prepare(
    "SELECT COUNT(*) FROM combustible WHERE fecha = ? AND alerta = 1"
);
$stmtA->execute([$fecha]);
$alertas = (int) $stmtA->fetchColumn();

$ganancia = $ingresos - $gasto;

echo json_encode([
    'ok'               => true,
    'fecha'            => $fecha,
    'ingresos_brutos'  => round($ingresos, 2),
    'gasto_combustible'=> round($gasto, 2),
    'ganancia_real'    => round($ganancia, 2),
    'estado'           => $ganancia >= 0 ? 'GANANCIA' : 'PÉRDIDA',
    'alertas_consumo'  => $alertas,
]);
