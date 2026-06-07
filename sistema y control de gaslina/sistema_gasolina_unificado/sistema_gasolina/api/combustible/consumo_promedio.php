<?php
// api/combustible/consumo_promedio.php — GET estadísticas de consumo (Wendy)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/db.php';

if (empty($_GET['id_camion'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'id_camion es obligatorio']);
    exit;
}

$idCamion = (int) $_GET['id_camion'];

$chk = $pdo->prepare(
    "SELECT id_camion, numero_placa, marca, modelo FROM camiones WHERE id_camion = ?"
);
$chk->execute([$idCamion]);
$camion = $chk->fetch();

if (!$camion) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'El camión no existe']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        COUNT(*)            AS total_cargas,
        SUM(litros)         AS total_litros,
        AVG(litros)         AS promedio_litros_por_carga,
        SUM(costo_total)    AS gasto_total_combustible,
        AVG(costo_total)    AS gasto_promedio_por_carga,
        AVG(precio_litro)   AS precio_promedio_litro,
        MAX(kilometraje)    AS kilometraje_actual,
        MIN(kilometraje)    AS kilometraje_inicial
    FROM combustible
    WHERE id_camion = ?
");
$stmt->execute([$idCamion]);
$datos = $stmt->fetch();

if ((int)$datos['total_cargas'] === 0) {
    echo json_encode(['ok' => true, 'mensaje' => 'No hay cargas registradas para este camión', 'camion' => $camion]);
    exit;
}

$kmRec = (int)$datos['kilometraje_actual'] - (int)$datos['kilometraje_inicial'];
$kmPorLitro = $datos['total_litros'] > 0
    ? round($kmRec / $datos['total_litros'], 2)
    : 0;

echo json_encode([
    'ok'                        => true,
    'camion'                    => $camion,
    'total_cargas'              => (int)$datos['total_cargas'],
    'total_litros'              => round($datos['total_litros'], 2),
    'promedio_litros_por_carga' => round($datos['promedio_litros_por_carga'], 2),
    'gasto_total_combustible'   => round($datos['gasto_total_combustible'], 2),
    'gasto_promedio_por_carga'  => round($datos['gasto_promedio_por_carga'], 2),
    'precio_promedio_litro'     => round($datos['precio_promedio_litro'], 2),
    'km_recorridos_total'       => $kmRec,
    'eficiencia_km_por_litro'   => $kmPorLitro,
]);
