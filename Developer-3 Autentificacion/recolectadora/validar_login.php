<?php
session_start();
include("conexion.php");

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE email='$email' AND password='$password'";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {

    $usuario = $resultado->fetch_assoc();

    $_SESSION['usuario'] = $usuario['nombre'];
    $_SESSION['role'] = $usuario['role'];

    if ($usuario['role'] == "ADMIN") {
        header("Location: admin.php");
    } else {
        header("Location: cobrador.php");
    }

} else {
    header("Location: login.php?error=1");
}

$conn->close();
?>