<?php
require_once("../config/database.php");

$hoy = date("Y-m-d");

$sql = "SELECT * FROM pagos WHERE fecha_pago = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hoy);
$stmt->execute();

$result = $stmt->get_result();
$pagos = [];

while ($row = $result->fetch_assoc()) {
    $pagos[] = $row;
}

echo json_encode($pagos);
?>