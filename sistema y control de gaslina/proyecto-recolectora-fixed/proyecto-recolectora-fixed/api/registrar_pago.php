<?php
/**
 * api/registrar_pago.php
 * API para registrar un nuevo pago
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validaciones
if (empty($data['cliente_id']) || empty($data['monto']) || empty($data['fecha_pago'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Cliente, monto y fecha de pago son obligatorios']);
    exit;
}

$db = getDB();

// Verificar que el cliente exista y esté activo
$stmt = $db->prepare("SELECT id, nombre, colonia_id FROM clientes WHERE id = ? AND estatus IN ('activo', 'pendiente')");
$stmt->execute([$data['cliente_id']]);
$cliente = $stmt->fetch();

if (!$cliente) {
    http_response_code(404);
    echo json_encode(['error' => 'Cliente no encontrado o está inactivo']);
    exit;
}

// Extraer mes y año de la fecha
$fecha = new DateTime($data['fecha_pago']);
$mes = (int)$fecha->format('n');
$anio = (int)$fecha->format('Y');

// Si se especificó mes pagado manualmente
if (!empty($data['mes_pagado'])) {
    $fechaMes = new DateTime($data['mes_pagado'] . '-01');
    $mes = (int)$fechaMes->format('n');
    $anio = (int)$fechaMes->format('Y');
}

try {
    // Verificar pago duplicado
    $stmt = $db->prepare("SELECT id FROM pagos WHERE cliente_id = ? AND mes = ? AND anio = ?");
    $stmt->execute([$data['cliente_id'], $mes, $anio]);
    
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Este cliente ya tiene un pago registrado para este mes']);
        exit;
    }
    
    // Insertar pago
    $stmt = $db->prepare("
        INSERT INTO pagos (cliente_id, usuario_id, monto, fecha_pago, anio, mes, metodo_pago, observaciones)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['cliente_id'],
        $_SESSION['user_id'],
        $data['monto'],
        $data['fecha_pago'],
        $anio,
        $mes,
        $data['metodo_pago'] ?? 'EFECTIVO',
        $data['observaciones'] ?? null
    ]);
    
    $pago_id = $db->lastInsertId();
    
    // Generar número de recibo
    $numero_recibo = 'R-' . date('Ymd') . '-' . str_pad($pago_id, 6, '0', STR_PAD_LEFT);
    
    $stmt = $db->prepare("INSERT INTO recibos (pago_id, numero_recibo) VALUES (?, ?)");
    $stmt->execute([$pago_id, $numero_recibo]);
    
    echo json_encode([
        'success' => true,
        'id' => $pago_id,
        'numero_recibo' => $numero_recibo,
        'message' => 'Pago registrado correctamente'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar pago: ' . $e->getMessage()]);
}