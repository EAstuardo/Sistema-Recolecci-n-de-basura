<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="login-container">
    <h2>Iniciar Sesión</h2>

    <form action="validar_login.php" method="POST">
        <input type="email" name="email" placeholder="Correo" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>

    <?php
    if (isset($_GET['error'])) {
        echo "<p style='color:red;'>Credenciales incorrectas</p>";
    }
    ?>
</div>

</body>
</html>