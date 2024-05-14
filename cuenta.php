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

        $stmt = $conexion->prepare("SELECT * FROM Usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<form method='post' action='modificar_perfil.php'>";
            while ($row = $result->fetch_assoc()) {
                
                echo "<tr>";
                echo "<td>ID</td>";
                echo "<td><input type='text' name='id_usuario' value='" . $row["id_usuario"] . "' readonly></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>Nombre</td>";
                echo "<td><input type='text' name='nombre_usuario' value='" . $row["nombre_usuario"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>Apellidos</td>";
                echo "<td><input type='text' name='apellidos_usuario' value='" . $row["apellidos_usuario"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>Email</td>";
                echo "<td><input type='email' name='email_usuario' value='" . $row["email_usuario"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>Método de Pago</td>";
                echo "<td><input type='text' name='metodo_pago' value='" . $row["metodo_pago"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";
                
                echo "<tr>";
                echo "<td>Tarjeta</td>";
                echo "<td><input type='text' name='tarjeta_usuario' value='" . $row["tarjeta_usuario"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";
                
                echo "<tr>";
                echo "<td>Caducidad Tarjeta</td>";
                echo "<td><input type='text' name='caducidad_tarjeta' value='" . $row["caducidad_tarjeta"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>CCV</td>";
                echo "<td><input type='text' name='CCV' value='" . $row["CCV"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>Teléfono</td>";
                echo "<td><input type='text' name='telefono_usuario' value='" . $row["telefono_usuario"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>Dirección</td>";
                echo "<td><input type='text' name='direccion_usuario' value='" . $row["direccion_usuario"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>Código Postal</td>";
                echo "<td><input type='text' name='cp_usuario' value='" . $row["cp_usuario"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>Población</td>";
                echo "<td><input type='text' name='poblacion_usuario' value='" . $row["poblacion_usuario"] . "'></td>";
                echo "</tr>";
                echo "<br>";
                echo "<br>";

                echo "<tr>";
                echo "<td>País</td>";
                echo "<td><input type='text' name='pais_usuario' value='" . $row["pais_usuario"] . "'></td>";
                echo "</tr>";
            }

            echo "<br>";
            echo "<br>";
            echo "<button type='submit' name='modificar'>Modificar</button>";
            
            echo "</form>";


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