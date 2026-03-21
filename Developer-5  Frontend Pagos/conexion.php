<?php
$host     = "localhost";
$dbname   = "basura";
$usuario  = "root";
$password = "";

$conexion = new mysqli($host, $usuario, $password, $dbname);
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexion: " . $conexion->connect_error]);
    exit;
}
?>