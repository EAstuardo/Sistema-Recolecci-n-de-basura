<?php
/**
 * includes/db.php
 * Conexión centralizada a la base de datos
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'recolectora_db');

// Deshabilitar reporte de errores en producción
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/**
 * Obtiene la conexión PDO a la base de datos
 * @return PDO
 * @throws PDOException
 */
function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]));
        }
    }
    
    return $pdo;
}

/**
 * Escapa y sanitiza un string para HTML
 * @param string $input
 * @return string
 */
function h($input): string {
    return htmlspecialchars($input ?? '', ENT_QUOTES, 'UTF-8');
}