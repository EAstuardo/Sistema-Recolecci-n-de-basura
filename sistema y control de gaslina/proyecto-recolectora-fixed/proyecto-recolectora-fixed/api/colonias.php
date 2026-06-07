<?php
/**
 * api/colonias.php
 * API REST para gestión de colonias
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
            $stmt = $db->query("
                SELECT c.*, COUNT(cl.id) as total_clientes 
                FROM colonias c
                LEFT JOIN clientes cl ON cl.colonia_id = c.id
                WHERE c.activo = 1
                GROUP BY c.id
                ORDER BY c.nombre ASC
            ");
            $colonias = $stmt->fetchAll();
            echo json_encode($colonias);
            break;
            
        case 'POST':
            if (!isAdmin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Solo administradores pueden crear colonias']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['nombre']) || !isset($data['tarifa_mensual'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nombre y tarifa mensual son obligatorios']);
                exit;
            }
            
            $stmt = $db->prepare("
                INSERT INTO colonias (nombre, descripcion, tarifa_mensual)
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'] ?? null,
                $data['tarifa_mensual']
            ]);
            
            echo json_encode(['success' => true, 'id' => $db->lastInsertId(), 'message' => 'Colonia creada correctamente']);
            break;
            
        case 'PUT':
            if (!isAdmin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Solo administradores pueden modificar colonias']);
                exit;
            }
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de colonia requerido']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $db->prepare("
                UPDATE colonias 
                SET nombre = ?, descripcion = ?, tarifa_mensual = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'] ?? null,
                $data['tarifa_mensual'],
                $id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Colonia actualizada correctamente']);
            break;
            
        case 'DELETE':
            if (!isAdmin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Solo administradores pueden eliminar colonias']);
                exit;
            }
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de colonia requerido']);
                exit;
            }
            
            // Verificar si tiene clientes asociados
            $stmt = $db->prepare("SELECT COUNT(*) FROM clientes WHERE colonia_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(['error' => 'No se puede eliminar: la colonia tiene clientes asociados']);
                exit;
            }
            
            $stmt = $db->prepare("DELETE FROM colonias WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Colonia eliminada correctamente']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}