<?php
/**
 * api/auth.php
 * Endpoint para autenticación de usuarios
 */

require_once '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header('Location: ../login.php?error=1');
    exit();
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['rol'];
        
        header('Location: ../index.php');
        exit();
    } else {
        header('Location: ../login.php?error=1');
        exit();
    }
} catch (PDOException $e) {
    error_log('Error de autenticación: ' . $e->getMessage());
    header('Location: ../login.php?error=1');
    exit();
}