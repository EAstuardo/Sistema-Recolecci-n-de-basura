<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

require_once "conexion.php";

$datos = json_decode(file_get_contents("php://input"), true);

$id_cliente    = isset($datos["id_cliente"])    ? intval($datos["id_cliente"]) : null;
$id_usuario    = isset($datos["id_usuario"])    ? intval($datos["id_usuario"]) : 1;
$monto         = isset($datos["monto"])         ? floatval($datos["monto"])    : null;
$fecha_pago    = isset($datos["fecha_pago"])    ? $datos["fecha_pago"]         : null;
$mes_pagado    = isset($datos["mes_pagado"])    ? $datos["mes_pagado"]         : date("Y-m");
$metodo_pago   = isset($datos["metodo_pago"])   ? strtoupper($datos["metodo_pago"]) : "EFECTIVO";
$observaciones = isset($datos["observaciones"]) ? $datos["observaciones"]      : null;

if (!$id_cliente || !$monto || !$fecha_pago) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan campos obligatorios"]);
    exit;
}

// Verificar pago duplicado
$verificar = $conexion->prepare("SELECT id_pago FROM pagos WHERE id_cliente = ? AND mes_pagado = ?");
$verificar->bind_param("is", $id_cliente, $mes_pagado);
$verificar->execute();
$verificar->store_result();

if ($verificar->num_rows > 0) {
    $verificar->close();
    http_response_code(409);
    echo json_encode(["error" => "Este cliente ya tiene un pago registrado para este mes"]);
    exit;
}
$verificar->close();

// Insertar pago
$stmt = $conexion->prepare("INSERT INTO pagos (id_cliente, id_usuario, monto, fecha_pago, mes_pagado, metodo_pago, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iidssss", $id_cliente, $id_usuario, $monto, $fecha_pago, $mes_pagado, $metodo_pago, $observaciones);

if ($stmt->execute()) {
    $nuevo_id = $conexion->insert_id;
    $stmt->close();
    echo json_encode(["success" => true, "id_pago" => $nuevo_id]);
} else {
    $error = $stmt->error;
    $stmt->close();
    http_response_code(500);
    echo json_encode(["error" => "Error al guardar: " . $error]);
}
?>