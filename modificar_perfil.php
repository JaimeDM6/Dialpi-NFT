<?php 
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: /login");
    exit;
}

include 'head.php';
include 'header.php';
?>
    <main>
        <div class="container">
        <h2>Mi perfil</h2>

        <?php

        $id_usuario = $_SESSION['id_usuario'];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            $nombre_usuario = $_POST['nombre_usuario'];
            $apellidos_usuario = $_POST['apellidos_usuario'];
            $email_usuario = $_POST['email_usuario'];
            $metodo_pago = $_POST['metodo_pago'];
            $tarjeta_usuario = $_POST['tarjeta_usuario'];
            $caducidad_tarjeta = $_POST['caducidad_tarjeta'];
            $CCV = $_POST['CCV'];
            $telefono_usuario = $_POST['telefono_usuario'];
            $direccion_usuario = $_POST['direccion_usuario'];
            $cp_usuario = $_POST['cp_usuario'];
            $poblacion_usuario = $_POST['poblacion_usuario'];
            $pais_usuario = $_POST['pais_usuario'];

            
            $stmt = $conexion->prepare("UPDATE Usuarios SET nombre_usuario=?, apellidos_usuario=?, email_usuario=?, metodo_pago=?, tarjeta_usuario=?, caducidad_tarjeta=?, CCV=?, telefono_usuario=?, direccion_usuario=?, cp_usuario=?, poblacion_usuario=?, pais_usuario=? WHERE id_usuario=?");
            
            // Vincular parámetros
            $stmt->bind_param("ssssssisssssi", $nombre_usuario, $apellidos_usuario, $email_usuario, $metodo_pago, $tarjeta_usuario, $caducidad_tarjeta, $CCV, $telefono_usuario, $direccion_usuario, $cp_usuario, $poblacion_usuario, $pais_usuario, $id_usuario);
            
            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo "Los datos se han actualizado correctamente.";
            } else {
                echo "Error al actualizar los datos: " . $stmt->error;
            }
            
            // Cerrar la consulta
            $stmt->close();


        } else {
            echo "No se encontraron resultados para el usuario actual.";
        }
        ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="script/script.js"></script>
</body>
</html>