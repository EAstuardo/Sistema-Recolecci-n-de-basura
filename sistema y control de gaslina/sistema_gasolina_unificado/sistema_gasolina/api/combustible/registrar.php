<?php
// api/combustible/registrar.php — POST nueva carga de combustible
// Unifica: validaciones de Wendy, lógica de alerta de Cesar, PDO de Herielis
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// ── Verificación de rol: solo ADMIN puede registrar combustible (Cesar) ──────
session_start();
if (empty($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'mensaje' => 'Solo el administrador puede registrar combustible']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

// Acepta tanto form-data como JSON (Herielis)
$isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
if ($isJson) {
    $body      = json_decode(file_get_contents('php://input'), true) ?? [];
    $idCamion  = (int)   ($body['id_camion']    ?? 0);
    $fecha     = trim($body['fecha']             ?? '');
    $litros    = (float) ($body['litros']         ?? 0);
    $precio    = (float) ($body['precio_litro']   ?? 0);
    $km        = (int)   ($body['kilometraje']    ?? 0);
    $kmRec     = (float) ($body['km_recorridos']  ?? 0);
    $tipo      = strtoupper(trim($body['tipo_combustible'] ?? 'DIESEL'));
    $estacion  = trim($body['estacion']            ?? '');
    $observ    = trim($body['observaciones']       ?? '');
} else {
    $idCamion  = (int)   ($_POST['id_camion']    ?? 0);
    $fecha     = trim($_POST['fecha']             ?? '');
    $litros    = (float) ($_POST['litros']         ?? 0);
    $precio    = (float) ($_POST['precio_litro']   ?? 0);
    $km        = (int)   ($_POST['kilometraje']    ?? 0);
    $kmRec     = (float) ($_POST['km_recorridos']  ?? 0);
    $tipo      = strtoupper(trim($_POST['tipo_combustible'] ?? 'DIESEL'));
    $estacion  = trim($_POST['estacion']            ?? '');
    $observ    = trim($_POST['observaciones']       ?? '');
}

// ── Validaciones (Wendy) ──────────────────────────────────────────────────────
if (!$idCamion || !$fecha) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Camión y fecha son obligatorios']);
    exit;
}
if ($litros <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Los litros deben ser mayores a 0']);
    exit;
}
if ($precio <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'El precio por litro debe ser mayor a 0']);
    exit;
}
if ($km < 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'El kilometraje no puede ser negativo']);
    exit;
}

if (!in_array($tipo, ['DIESEL', 'GASOLINA'])) $tipo = 'DIESEL';

// ── Verificar que el camión existe ───────────────────────────────────────────
$chk = $pdo->prepare("SELECT id_camion FROM camiones WHERE id_camion = ?");
$chk->execute([$idCamion]);
if (!$chk->fetch()) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'Camión no encontrado']);
    exit;
}

// ── Cálculos (Cesar) ──────────────────────────────────────────────────────────
$costoTotal  = round($litros * $precio, 2);
$rendimiento = $kmRec > 0 && $litros > 0 ? round($kmRec / $litros, 2) : 0;

// Alerta si consumo anormal: rendimiento < 6 km/L (cuando hay km recorridos)
// o si no hay km, alerta si el costo supera Q 2,000 en una sola carga
$alerta = 0;
if ($kmRec > 0 && $rendimiento > 0 && $rendimiento < 6) {
    $alerta = 1;
} elseif ($kmRec === 0.0 && $costoTotal > 2000) {
    $alerta = 1;
}

$observ = $observ ?: null;
$estacion = $estacion ?: null;
$idUsuario = $_SESSION['id_usuario'];

try {
    $stmt = $pdo->prepare("
        INSERT INTO combustible
            (id_camion, id_usuario, fecha, litros, kilometraje, km_recorridos,
             precio_litro, costo_total, tipo_combustible, estacion, alerta, observaciones)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $idCamion, $idUsuario, $fecha, $litros, $km, $kmRec,
        $precio, $costoTotal, $tipo, $estacion, $alerta, $observ
    ]);

    $resp = [
        'ok'           => true,
        'mensaje'      => 'Carga de combustible registrada correctamente',
        'id_combustible' => $pdo->lastInsertId(),
        'costo_total'  => $costoTotal,
        'rendimiento'  => $rendimiento,
    ];
    if ($alerta) {
        $resp['alerta'] = 'CONSUMO ANORMAL detectado';
    }

    echo json_encode($resp);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
