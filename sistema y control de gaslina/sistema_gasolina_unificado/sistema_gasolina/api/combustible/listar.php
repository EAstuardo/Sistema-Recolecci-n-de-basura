<?php
// api/combustible/listar.php — GET historial de combustible (Herielis + Wendy)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/db.php';

$idCamion = isset($_GET['id_camion']) ? (int) $_GET['id_camion'] : null;

try {
    $where = $idCamion ? "WHERE cb.id_camion = " . (int)$idCamion : "";

    $stmt = $pdo->query("
        SELECT
            cb.id_combustible,
            cb.id_camion,
            cb.fecha,
            cb.litros,
            cb.precio_litro,
            cb.costo_total,
            cb.km_recorridos,
            cb.rendimiento,
            cb.tipo_combustible,
            cb.estacion,
            cb.alerta,
            cb.observaciones,
            cb.created_at,
            cam.numero_placa,
            cam.marca,
            cam.modelo,
            u.nombre AS registrado_por
        FROM combustible cb
        LEFT JOIN camiones cam ON cam.id_camion = cb.id_camion
        LEFT JOIN usuarios  u  ON u.id_usuario  = cb.id_usuario
        $where
        ORDER BY cb.fecha DESC, cb.created_at DESC
    ");

    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
