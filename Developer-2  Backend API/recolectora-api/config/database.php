<?php
$host = "localhost";
$db = "recolectora_db";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión"]));
}

header("Content-Type: application/json");
?>