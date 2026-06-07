<?php
/**
 * pages/usuarios.php
 * Gestión de usuarios (solo administradores)
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('ADMIN');
$page_title = 'Usuarios';
$page_subtitle = 'Gestiona los usuarios del sistema';

$db = getDB();
$mensaje = null;
$tipo_mensaje = '';

// Crear usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'COBRADOR';
    
    $errores = [];
    if (empty($nombre)) $errores[] = 'El nombre es obligatorio';
    if (empty($email)) $errores[] = 'El correo es obligatorio';
    if (empty($password)) $errores[] = 'La contraseña es obligatoria';
    if (!in_array($rol, ['ADMIN', 'COBRADOR', 'OPERADOR'])) $errores[] = 'Rol inválido';
    
    if (empty($errores)) {
        try {
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $mensaje = 'El correo ya está registrado';
                $tipo_mensaje = 'error';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $email, $passwordHash, $rol]);
                $mensaje = 'Usuario creado correctamente';
                $tipo_mensaje = 'success';
            }
        } catch (PDOException $e) {
            $mensaje = 'Error al crear usuario: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = implode(', ', $errores);
        $tipo_mensaje = 'error';
    }
}

// Obtener usuarios
$usuarios = $db->query("
    SELECT id, nombre, email, rol, activo, created_at 
    FROM usuarios 
    ORDER BY created_at DESC
")->fetchAll();

include '../includes/header.php';
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo h($mensaje); ?></div>
<?php endif; ?>

<div class="form-row">
    <!-- Formulario crear usuario -->
    <div class="card" style="flex: 1;">
        <div class="card-title">➕ Crear Nuevo Usuario</div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="crear">
            
            <div class="form-group">
                <label for="nombre">Nombre Completo <span class="required">*</span></label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol" class="form-control">
                    <option value="ADMIN">Administrador</option>
                    <option value="COBRADOR" selected>Cobrador</option>
                    <option value="OPERADOR">Operador</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">✓ Crear Usuario</button>
        </form>
    </div>
    
    <!-- Lista de usuarios -->
    <div class="card" style="flex: 2;">
        <div class="card-title">
            <span>👥 Usuarios del Sistema</span>
            <span class="badge badge-info"><?php echo count($usuarios); ?></span>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><strong><?php echo h($usuario['nombre']); ?></strong></td>
                            <td><?php echo h($usuario['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $usuario['rol'] === 'ADMIN' ? 'warning' : 'info'; 
                                ?>">
                                    <?php echo $usuario['rol']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $usuario['activo'] ? 'success' : 'danger'; ?>">
                                    <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="text-muted"><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>