<?php
session_start();
require_once 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: /login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta de Usuario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <ul class="menu">
                <li><a href="#cuenta">Cuenta</a></li>
                <li><a href="#direccion">Dirección</a></li>
                <li><a href="#seguridad">Seguridad</a></li>
                <li><a href="#metodo-pago">Método de Pago</a></li>
            </ul>
        </div>
        <div class="content">
            <h2 id="metodo-pago">Método de Pago</h2>
            <?php
                // Ejemplo de número de tarjeta (en una aplicación real, este dato vendría de una base de datos)
                $numeroTarjeta = "1234567812345678";

                // Obtener los últimos 4 dígitos de la tarjeta
                $ultimosDigitos = substr($numeroTarjeta, -4);
            ?>
            <div class="tarjeta">
                <p>Tarjeta terminada en *****<?php echo $ultimosDigitos; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
