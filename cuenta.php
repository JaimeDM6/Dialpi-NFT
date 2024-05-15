<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Perfil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
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
        <div class="perfil-container-left">
            <h2>Mi perfil</h2>

            <?php
            $id_usuario = $_SESSION['id_usuario'];

            $stmt = $conexion->prepare("SELECT * FROM Usuarios WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<form class='perfil-form' method='post' action='modificar_perfil.php'>";

                while ($row = $result->fetch_assoc()) {
                    echo "<input type='hidden' name='id_usuario' value='" . $row["id_usuario"] . "'>";

                    // Información Personal
                    echo "<fieldset class='perfil-section'>";
                    echo "<legend>Información Personal</legend>";

                    echo "<label class='perfil-label' for='nombre_usuario'>Nombre:</label>";
                    echo "<input class='perfil-input' type='text' id='nombre_usuario' name='nombre_usuario' value='" . $row["nombre_usuario"] . "' readonly>";

                    echo "<label class='perfil-label' for='apellidos_usuario'>Apellidos:</label>";
                    echo "<input class='perfil-input' type='text' id='apellidos_usuario' name='apellidos_usuario' value='" . $row["apellidos_usuario"] . "' readonly>";

                    echo "<label class='perfil-label' for='email_usuario'>Email:</label>";
                    echo "<input class='perfil-input' type='email' id='email_usuario' name='email_usuario' value='" . $row["email_usuario"] . "' readonly>";

                    echo "<button class='perfil-edit-button'>Editar</button>";
                    echo "</fieldset>";

                    // Dirección
                    echo "<fieldset class='perfil-section'>";
                    echo "<legend>Dirección</legend>";

                    echo "<label class='perfil-label' for='direccion_usuario'>Dirección:</label>";
                    echo "<input class='perfil-input' type='text' id='direccion_usuario' name='direccion_usuario' value='" . $row["direccion_usuario"] . "' readonly>";

                    echo "<label class='perfil-label' for='cp_usuario'>Código Postal:</label>";
                    echo "<input class='perfil-input' type='text' id='cp_usuario' name='cp_usuario' value='" . $row["cp_usuario"] . "' readonly>";

                    echo "<label class='perfil-label' for='poblacion_usuario'>Población:</label>";
                    echo "<input class='perfil-input' type='text' id='poblacion_usuario' name='poblacion_usuario' value='" . $row["poblacion_usuario"] . "' readonly>";

                    echo "<label class='perfil-label' for='pais_usuario'>País:</label>";
                    echo "<input class='perfil-input' type='text' id='pais_usuario' name='pais_usuario' value='" . $row["pais_usuario"] . "' readonly>";

                    echo "<button class='perfil-edit-button'>Editar</button>";
                    echo "</fieldset>";

                    // Seguridad
                    echo "<fieldset class='perfil-section'>";
                    echo "<legend>Seguridad</legend>";

                    echo "<label class='perfil-label' for='contrasena'>Contraseña:</label>";
                    echo "<input class='perfil-input' type='password' id='contrasena' name='contrasena' readonly>";

                    echo "<label class='perfil-label' for='confirmar_contrasena'>Confirmar Contraseña:</label>";
                    echo "<input class='perfil-input' type='password' id='confirmar_contrasena' name='confirmar_contrasena' readonly>";

                    echo "<button class='perfil-edit-button'>Editar</button>";
                    echo "</fieldset>";

                    // Métodos de Pago
                    echo "<fieldset class='perfil-section'>";
                    echo "<legend>Métodos de Pago</legend>";

                    echo "<label class='perfil-label' for='metodo_pago'>Método de Pago:</label>";
                    echo "<input class='perfil-input' type='text' id='metodo_pago' name='metodo_pago' value='" . $row["metodo_pago"] . "' readonly>";

                    echo "<label class='perfil-label' for='tarjeta_usuario'>Tarjeta:</label>";
                    echo "<input class='perfil-input' type='text' id='tarjeta_usuario' name='tarjeta_usuario' value='" . $row["tarjeta_usuario"] . "' readonly>";

                    echo "<label class='perfil-label' for='caducidad_tarjeta'>Caducidad Tarjeta:</label>";
                    echo "<input class='perfil-input' type='text' id='caducidad_tarjeta' name='caducidad_tarjeta' value='" . $row["caducidad_tarjeta"] . "' readonly>";

                    echo "<label class='perfil-label' for='CCV'>CCV:</label>";
                    echo "<input class='perfil-input' type='text' id='CCV' name='CCV' value='" . $row["CCV"] . "' readonly>";

                    echo "<button class='perfil-edit-button'>Editar</button>";
                    echo "</fieldset>";

                    // Eliminar Cuenta
                    echo "<fieldset class='perfil-section'>";
                    echo "<legend>Eliminar Cuenta</legend>";

                    echo "<label class='perfil-label' for='confirmar_eliminar'>Confirmar Eliminación de Cuenta:</label>";
                    echo "<input class='perfil-checkbox' type='checkbox' id='confirmar_eliminar' name='confirmar_eliminar'>";

                    echo "</fieldset>";
                }

                echo "<button class='perfil-save-button' type='submit' name='modificar'>Guardar</button>";
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
