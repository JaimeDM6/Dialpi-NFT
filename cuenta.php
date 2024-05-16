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
    
    if (!isset($_SESSION['usuario'])) {
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
            $id_usuario = $_SESSION['usuario']['id'];

            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modificar'])) {
                if (isset($_POST['confirmar_eliminar']) && $_POST['confirmar_eliminar'] == 'on') {
                    // Eliminar cuenta
                    $stmt = $conexion->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
                    $stmt->bind_param("i", $id_usuario);

                    if ($stmt->execute()) {
                        session_destroy();
                        header("Location: /login");
                        exit;
                    } else {
                        echo "Error al eliminar la cuenta: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    // Actualizar cuenta
                    $nombre_usuario = $_POST['nombre_usuario'];
                    $apellidos_usuario = $_POST['apellidos_usuario'];
                    $email_usuario = $_POST['email_usuario'];
                    $direccion_usuario = $_POST['direccion_usuario'];
                    $cp_usuario = $_POST['cp_usuario'];
                    $poblacion_usuario = $_POST['poblacion_usuario'];
                    $pais_usuario = $_POST['pais_usuario'];
                    $metodo_pago = $_POST['metodo_pago'];
                    $tarjeta_usuario = $_POST['tarjeta_usuario'];
                    $caducidad_tarjeta = $_POST['caducidad_tarjeta'];
                    $CCV = $_POST['CCV'];

                    $stmt = $conexion->prepare("UPDATE Usuarios SET nombre_usuario=?, apellidos_usuario=?, email_usuario=?, direccion_usuario=?, cp_usuario=?, poblacion_usuario=?, pais_usuario=?, metodo_pago=?, tarjeta_usuario=?, caducidad_tarjeta=?, CCV=? WHERE id_usuario=?");
                    $stmt->bind_param("sssssssssssi", $nombre_usuario, $apellidos_usuario, $email_usuario, $direccion_usuario, $cp_usuario, $poblacion_usuario, $pais_usuario, $metodo_pago, $tarjeta_usuario, $caducidad_tarjeta, $CCV, $id_usuario);

                    if ($stmt->execute()) {
                        echo "Datos actualizados correctamente.";
                    } else {
                        echo "Error al actualizar los datos: " . $stmt->error;
                    }
                }
                    // Verificar CCV
                    $stmt_ccv = $conexion->prepare("SELECT CCV FROM Usuarios WHERE id_usuario = ?");
                    $stmt_ccv->bind_param("i", $id_usuario);
                    $stmt_ccv->execute();
                    $result_ccv = $stmt_ccv->get_result();

                    if ($result_ccv->num_rows > 0) {
                        $row_ccv = $result_ccv->fetch_assoc();
                        $ccv_correcto = $row_ccv["CCV"];

                        if ($ccv_correcto == $CCV) {
                            // CCV correcto, proceder con la actualización
                            $stmt = $conexion->prepare("UPDATE Usuarios SET nombre_usuario=?, apellidos_usuario=?, email_usuario=?, direccion_usuario=?, cp_usuario=?, poblacion_usuario=?, pais_usuario=?, metodo_pago=?, tarjeta_usuario=?, caducidad_tarjeta=?, CCV=? WHERE id_usuario=?");
                            $stmt->bind_param("sssssssssssi", $nombre_usuario, $apellidos_usuario, $email_usuario, $direccion_usuario, $cp_usuario, $poblacion_usuario, $pais_usuario, $metodo_pago, $tarjeta_usuario, $caducidad_tarjeta, $CCV, $id_usuario);

                            if ($stmt->execute()) {
                                echo "Datos actualizados correctamente.";
                            } else {
                                echo "Error al actualizar los datos: " . $stmt->error;
                            }
                        } else {
                            // CCV incorrecto
                            echo "El CCV introducido no coincide con el registrado en la base de datos.";
                        }
                    } else {
                        echo "Error al obtener el CCV de la base de datos.";
                    }

                    $stmt_ccv->close();
                    $stmt->close();
                }


            $stmt = $conexion->prepare("SELECT * FROM Usuarios WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo "<form class='perfil-form' method='post' action=''>";
                while ($row = $result->fetch_assoc()) {
                    echo "<input type='hidden' name='id_usuario' value='" . $row["id_usuario"] . "'>";
                    // Información Personal
                    echo "<fieldset class='perfil-section' id='informacion-personal'>";
                    echo "<legend>Información Personal</legend>";
                    echo "<label class='perfil-label' for='nombre_usuario'>Nombre:</label>";
                    echo "<input class='perfil-input' type='text' id='nombre_usuario' name='nombre_usuario' value='" . $row["nombre_usuario"] . "' readonly>";
                    echo "<label class='perfil-label' for='apellidos_usuario'>Apellidos:</label>";
                    echo "<input class='perfil-input' type='text' id='apellidos_usuario' name='apellidos_usuario' value='" . $row["apellidos_usuario"] . "' readonly>";
                    echo "<label class='perfil-label' for='email_usuario'>Email:</label>";
                    echo "<input class='perfil-input' type='email' id='email_usuario' name='email_usuario' value='" . $row["email_usuario"] . "' readonly>";
                    echo "<button type='button' class='perfil-edit-button' onclick='toggleEdit(\"informacion-personal\")'>Editar</button>";
                    echo "</fieldset>";
                    // Dirección
                    echo "<fieldset class='perfil-section' id='direccion'>";
                    echo "<legend>Dirección</legend>";
                    echo "<label class='perfil-label' for='direccion_usuario'>Dirección:</label>";
                    echo "<input class='perfil-input' type='text' id='direccion_usuario' name='direccion_usuario' value='" . $row["direccion_usuario"] . "' readonly>";
                    echo "<label class='perfil-label' for='cp_usuario'>Código Postal:</label>";
                    echo "<input class='perfil-input' type='text' id='cp_usuario' name='cp_usuario' value='" . $row["cp_usuario"] . "' readonly>";
                    echo "<label class='perfil-label' for='poblacion_usuario'>Población:</label>";
                    echo "<input class='perfil-input' type='text' id='poblacion_usuario' name='poblacion_usuario' value='" . $row["poblacion_usuario"] . "' readonly>";
                    echo "<label class='perfil-label' for='pais_usuario'>País:</label>";
                    echo "<input class='perfil-input' type='text' id='pais_usuario' name='pais_usuario' value='" . $row["pais_usuario"] . "' readonly>";
                    echo "<button type='button' class='perfil-edit-button' onclick='toggleEdit(\"direccion\")'>Editar</button>";
                    echo "</fieldset>";
                    // Métodos de Pago
                    echo "<fieldset class='perfil-section' id='metodos-pago'>";
                    echo "<legend>Métodos de Pago</legend>";
                    echo "<label class='perfil-label' for='metodo_pago'>Método de Pago:</label>";
                    echo "<input class='perfil-input' type='text' id='metodo_pago' name='metodo_pago' value='" . $row["metodo_pago"] . "' readonly>";
                    echo "<label class='perfil-label' for='tarjeta_usuario'>Tarjeta:</label>";
                    
                    $tarjeta_oculta = "************" . substr($row["tarjeta_usuario"], -4); // Muestra solo los últimos 4 dígitos
                    echo "<input class='perfil-input' type='text' id='tarjeta_usuario' name='tarjeta_usuario' value='" . $tarjeta_oculta . "' readonly>";
                    echo "<label class='perfil-label' for='caducidad_tarjeta'>Caducidad Tarjeta:</label>";
                    echo "<input class='perfil-input' type='text' id='caducidad_tarjeta' name='caducidad_tarjeta' value='" . $row["caducidad_tarjeta"] . "' readonly>";
                    echo "<button type='button' class='perfil-edit-button' onclick='editMetodosPago(\"metodos-pago\")'>Editar</button>";
                    echo "</fieldset>";
                    // Eliminar Cuenta
                    echo "<fieldset class='perfil-section' id='eliminar-cuenta'>";
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
    <script>
        var intentos = 0;
        function toggleEdit(sectionId) {
            var section = document.getElementById(sectionId);
            var inputs = section.querySelectorAll('input');
            if (section.classList.contains('edit-mode')) {
                // Disable edit mode
                section.classList.remove('edit-mode');
                inputs.forEach(function(input) {
                    input.setAttribute('readonly', true);
                    input.classList.remove('editable');
                });
            } else {
                // Enable edit mode
                section.classList.add('edit-mode');
                inputs.forEach(function(input) {
                    input.removeAttribute('readonly');
                    input.classList.add('editable');
                });
            }
        }
        function editMetodosPago(sectionId) {
            var section = document.getElementById(sectionId);
            var tarjetaInput = section.querySelector('#tarjeta_usuario');
            
            // Función para solicitar CCV
            function promptCCV() {
                return prompt("Por favor, introduzca el CCV para editar los datos:");
            }
            // Función para validar el CCV
            function validarCCV(ccv) {
                var ccvCorrecto = '<?php echo json_encode($row["CCV"]); ?>'; // CCV correcto obtenido desde PHP
                return ccv === ccvCorrecto;
            }
            // Función para mostrar mensaje de error
            function mostrarMensajeError() {
                alert("CCV introducido no es correcto. Intentos restantes: " + (3 - intentos));
            }
            // Función para manejar la edición
            function handleEdit() {
                var ccv = promptCCV();
                if (ccv !== null) { // Si el usuario no cancela la ventana emergente
                    if (validarCCV(ccv)) {
                        // Mostrar todos los datos
                        tarjetaInput.value = '<?php echo $row["tarjeta_usuario"]; ?>';
                        toggleEdit(sectionId);
                    } else {
                        // Mensaje de error y contador de intentos
                        intentos++;
                        if (intentos < 3) {
                            mostrarMensajeError();
                            handleEdit(); // Volver a solicitar el CCV
                        } else {
                            window.location.href = "/"; // Redirigir a la página inicial después de 3 intentos fallidos
                        }
                    }
                }
            }
            var intentos = 0;
            handleEdit(); // Iniciar el proceso de edición
        }
    </script>
</body>
</html>