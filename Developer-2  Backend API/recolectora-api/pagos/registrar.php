<?php
require_once("../config/database.php");

$data = json_decode(file_get_contents("php://input"));

// Validar campos obligatorios
if (
    empty($data->id_cliente) ||
    empty($data->id_usuario) ||
    empty($data->monto) ||
    empty($data->mes_pagado)
) {
    echo json_encode(["error" => "Campos obligatorios faltantes"]);
    exit;
}

// Validar monto
if ($data->monto <= 0) {
    echo json_encode(["error" => "Monto inválido"]);
    exit;
}

// Verificar que el cliente exista
$checkCliente = $conn->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ?");
$checkCliente->bind_param("i", $data->id_cliente);
$checkCliente->execute();
$resultCliente = $checkCliente->get_result();

if ($resultCliente->num_rows == 0) {
    echo json_encode(["error" => "El cliente no existe"]);
    exit;
}

// Verificar que el usuario exista
$checkUsuario = $conn->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
$checkUsuario->bind_param("i", $data->id_usuario);
$checkUsuario->execute();
$resultUsuario = $checkUsuario->get_result();

if ($resultUsuario->num_rows == 0) {
    echo json_encode(["error" => "El usuario no existe"]);
    exit;
}

// Fecha automática
$fecha = date("Y-m-d");

// Insertar pago
$sql = "INSERT INTO pagos (id_cliente, id_usuario, monto, fecha_pago, mes_pagado)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iidss",
    $data->id_cliente,
    $data->id_usuario,
    $data->monto,
    $fecha,
    $data->mes_pagado
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Pago registrado correctamente"]);
} else {
    echo json_encode(["error" => "Error al registrar pago"]);
}
?>