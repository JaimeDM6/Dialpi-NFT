<?php
$usuario = 'www-data';
$contraseña = '2ASIR_Grupo3';
$hostname = 'localhost';
$database = 'DialpiNFT';

$conexion = new mysqli($hostname, $usuario, $contraseña, $database);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

mysqli_set_charset($conexion, "utf8");