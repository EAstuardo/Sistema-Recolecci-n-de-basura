<?php
/**
 * api/pagos.php
 * API REST para consulta de pagos
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

try {
    switch ($method) {
        case 'GET':
            $cliente_id = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
            $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : 0;
            $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : 0;
            
            $sql = "
                SELECT p.*, c.nombre, c.apellido, col.nombre as colonia, u.nombre as cobrador
                FROM pagos p
                JOIN clientes c ON c.id = p.cliente_id
                JOIN colonias col ON col.id = c.colonia_id
                JOIN usuarios u ON u.id = p.usuario_id
                WHERE 1=1
            ";
            $params = [];
            
            if ($cliente_id > 0) {
                $sql .= " AND p.cliente_id = ?";
                $params[] = $cliente_id;
            }
            
            if ($mes > 0 && $mes <= 12) {
                $sql .= " AND p.mes = ?";
                $params[] = $mes;
            }
            
            if ($anio > 0) {
                $sql .= " AND p.anio = ?";
                $params[] = $anio;
            }
            
            $sql .= " ORDER BY p.fecha_pago DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $pagos = $stmt->fetchAll();
            
            echo json_encode($pagos);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}