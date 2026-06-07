<?php
/**
 * api/clientes.php
 * API REST para gestión de clientes
 * Métodos: GET, POST, PUT, DELETE
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Verificar autenticación
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

try {
    switch ($method) {
        case 'GET':
            // Obtener clientes con filtros
            $filtro = $_GET['filtro'] ?? '';
            $colonia_id = isset($_GET['colonia_id']) ? (int)$_GET['colonia_id'] : 0;
            $estatus = $_GET['estatus'] ?? '';
            
            $sql = "SELECT c.*, col.nombre as colonia_nombre, col.tarifa_mensual 
                    FROM clientes c 
                    JOIN colonias col ON col.id = c.colonia_id 
                    WHERE 1=1";
            $params = [];
            
            if ($filtro) {
                $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.telefono LIKE ? OR c.email LIKE ?)";
                $like = "%$filtro%";
                $params = array_merge($params, [$like, $like, $like, $like]);
            }
            
            if ($colonia_id > 0) {
                $sql .= " AND c.colonia_id = ?";
                $params[] = $colonia_id;
            }
            
            if ($estatus && in_array($estatus, ['activo', 'inactivo', 'pendiente'])) {
                $sql .= " AND c.estatus = ?";
                $params[] = $estatus;
            }
            
            $sql .= " ORDER BY c.created_at DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll();
            
            echo json_encode($clientes);
            break;
            
        case 'POST':
            // Crear nuevo cliente
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['nombre']) || empty($data['apellido']) || empty($data['colonia_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nombre, apellido y colonia son obligatorios']);
                exit;
            }
            
            $stmt = $db->prepare("
                INSERT INTO clientes (nombre, apellido, telefono, email, direccion, referencia, colonia_id, estatus)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['telefono'] ?? null,
                $data['email'] ?? null,
                $data['direccion'] ?? null,
                $data['referencia'] ?? null,
                $data['colonia_id'],
                $data['estatus'] ?? 'activo'
            ]);
            
            echo json_encode(['success' => true, 'id' => $db->lastInsertId(), 'message' => 'Cliente creado correctamente']);
            break;
            
        case 'PUT':
            // Actualizar cliente
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de cliente requerido']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $db->prepare("
                UPDATE clientes 
                SET nombre = ?, apellido = ?, telefono = ?, email = ?, direccion = ?, referencia = ?, colonia_id = ?, estatus = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['telefono'] ?? null,
                $data['email'] ?? null,
                $data['direccion'] ?? null,
                $data['referencia'] ?? null,
                $data['colonia_id'],
                $data['estatus'] ?? 'activo',
                $id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente']);
            break;
            
        case 'DELETE':
            // Soft delete: solo marcar como inactivo (no eliminar físicamente)
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de cliente requerido']);
                exit;
            }
            
            // Solo admin puede desactivar clientes
            if (!isAdmin()) {
                http_response_code(403);
                echo json_encode(['error' => 'No tienes permisos para desactivar clientes']);
                exit;
            }
            
            // Verificar si el cliente existe
            $stmt = $db->prepare("SELECT id FROM clientes WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Cliente no encontrado']);
                exit;
            }
            
            // Desactivar cliente (soft delete)
            $stmt = $db->prepare("UPDATE clientes SET estatus = 'inactivo' WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Cliente desactivado correctamente']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}