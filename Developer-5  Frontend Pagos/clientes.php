<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "conexion.php";

$mes_actual = isset($_GET['mes']) ? $_GET['mes'] : date("Y-m");

$sql = "
    SELECT
        c.id_cliente,
        CONCAT(c.nombre, ' ', c.apellido) AS nombre_completo,
        c.telefono,
        c.direccion,
        col.nombre AS colonia,
        col.tarifa_mensual AS monto,
        CASE
            WHEN p.id_pago IS NOT NULL THEN 'pagado'
            ELSE 'pendiente'
        END AS estado,
        p.fecha_pago,
        p.metodo_pago,
        p.mes_pagado
    FROM clientes c
    INNER JOIN colonias col ON c.id_colonia = col.id_colonia
    LEFT JOIN pagos p
        ON p.id_cliente = c.id_cliente
        AND p.mes_pagado = ?
    WHERE c.activo = 1
    ORDER BY c.nombre ASC
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $mes_actual);
$stmt->execute();
$result = $stmt->get_result();
$clientes = $result->fetch_all(MYSQLI_ASSOC) ?? [];

echo json_encode($clientes);
?>