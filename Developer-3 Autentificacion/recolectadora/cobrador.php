<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "COBRADOR") {
    header("Location: login.php");
    exit();
}
?>

<h1>Bienvenido Cobrador</h1>
<a href="logout.php">Cerrar sesión</a>