<?php
require_once("../config/database.php");

$data = json_decode(file_get_contents("php://input"));

if (empty($data->nombre) || empty($data->tarifa_mensual)) {
    echo json_encode(["error" => "Nombre y tarifa son obligatorios"]);
    exit;
}

$sql = "INSERT INTO colonias (nombre, descripcion, tarifa_mensual)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssd",
    $data->nombre,
    $data->descripcion,
    $data->tarifa_mensual
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Colonia creada correctamente"]);
} else {
    echo json_encode(["error" => "Error al crear colonia"]);
}
?>