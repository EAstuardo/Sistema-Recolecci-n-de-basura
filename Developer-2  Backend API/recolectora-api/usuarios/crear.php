<?php
require_once("../config/database.php");

$data = json_decode(file_get_contents("php://input"));

// Validar que los campos no estén vacíos 
if (
    empty($data->nombre) ||
    empty($data->email) ||
    empty($data->password) ||
    empty($data->rol)
) {
    echo json_encode(["error" => "Campos obligatorios faltantes"]);
    exit;
}

// Validar rol permitido
if ($data->rol !== "ADMIN" && $data->rol !== "COBRADOR") {
    echo json_encode(["error" => "Rol inválido"]);
    exit;
}

// Verificar si el email ya existe
$check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
$check->bind_param("s", $data->email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["error" => "El email ya está registrado"]);
    exit;
}

// Encriptar contraseña (muy importante)
$passwordHash = password_hash($data->password, PASSWORD_DEFAULT);

// Insertar usuario
$sql = "INSERT INTO usuarios (nombre, email, password, rol)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss",
    $data->nombre,
    $data->email,
    $passwordHash,
    $data->rol
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Usuario creado correctamente"]);
} else {
    echo json_encode(["error" => "Error al crear usuario"]);
}
?>