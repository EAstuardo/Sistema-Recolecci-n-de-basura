<?php
require_once("../config/database.php");

$data = json_decode(file_get_contents("php://input"));

if (empty($data->nombre) || empty($data->apellido) || empty($data->id_colonia)) {
    echo json_encode(["error" => "Campos obligatorios faltantes"]);
    exit;
}

# ----------------------Verificar colonia-------------------------------
$check = $conn->prepare("SELECT id_colonia FROM colonias WHERE id_colonia = ?");
$check->bind_param("i", $data->id_colonia);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["error" => "Colonia no existe"]);
    exit;
}

$sql = "INSERT INTO clientes (nombre, apellido, telefono, direccion, id_colonia)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi",
    $data->nombre,
    $data->apellido,
    $data->telefono,
    $data->direccion,
    $data->id_colonia
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Cliente creado correctamente"]);
} else {
    echo json_encode(["error" => "Error al crear cliente"]);
}
?>