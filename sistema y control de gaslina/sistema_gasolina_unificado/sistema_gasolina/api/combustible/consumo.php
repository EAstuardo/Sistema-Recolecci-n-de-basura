<?php
// api/combustible/consumo.php — GET consumo histórico de un camión (Wendy)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/db.php';

if (empty($_GET['id_camion'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'id_camion es obligatorio']);
    exit;
}

$idCamion = (int) $_GET['id_camion'];

// Verificar que el camión exista
$chk = $pdo->prepare(
    "SELECT id_camion, numero_placa, marca, modelo, estado FROM camiones WHERE id_camion = ?"
);
$chk->execute([$idCamion]);
$camion = $chk->fetch();

if (!$camion) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'El camión no existe']);
    exit;
}

// Historial de cargas
$stmt = $pdo->prepare("
    SELECT id_combustible, fecha, litros, kilometraje, precio_litro,
           costo_total, tipo_combustible, observaciones
    FROM combustible
    WHERE id_camion = ?
    ORDER BY fecha DESC
");
$stmt->execute([$idCamion]);
$cargas = $stmt->fetchAll();

echo json_encode([
    'ok'           => true,
    'camion'       => $camion,
    'total_cargas' => count($cargas),
    'cargas'       => $cargas
]);
