<?php
$host = "localhost";
$user = "root";   // si tienes contraseña, ponla aquí
$pass = "";
$db   = "tienda_nueva";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
