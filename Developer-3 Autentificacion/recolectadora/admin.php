<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login.php");
    exit();
}
?>

<h1>Bienvenido Administrador</h1>
<a href="logout.php">Cerrar sesión</a>