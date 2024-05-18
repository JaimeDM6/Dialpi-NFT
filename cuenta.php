<?php
    $title = 'Mi cuenta - Dialpi NFT';
    session_start();
    require_once 'conexion.php';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
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
                    $id_usuario = $_POST['id_usuario'];
                    $nombre_usuario = $_POST['nombre_usuario'];
                    $apellidos_usuario = $_POST['apellidos_usuario'];
                    $email_usuario = $_POST['email_usuario'];
                    $direccion_usuario = $_POST['direccion_usuario'];
                    $cp_usuario = $_POST['cp_usuario'];
                    $poblacion_usuario = $_POST['poblacion_usuario'];
                    $estado_provincia = $_POST['estado_provincia'];
                    $pais_usuario = $_POST['pais_usuario'];
                    $metodo_pago = $_POST['metodo_pago'];
                    $tarjeta_usuario = $_POST['tarjeta_usuario'];
                    $caducidad_tarjeta = $_POST['caducidad_tarjeta'];
                    $CCV = $_POST['CCV'];
                    $telefono_usuario = $_POST['telefono_usuario'];
                    $ruta_perfil = $_POST['ruta_perfil'];
                    $unidades_vendidas = $_POST['unidades_vendidas'];
                    $unidades_compradas = $_POST['unidades_compradas'];
                    $NFT_comprado = $_POST['NFT_comprado'];
                    $NFT_vendido = $_POST['NFT_vendido'];
                
                    $stmt = $conexion->prepare("UPDATE Usuarios SET nombre_usuario=?, apellidos_usuario=?, email_usuario=?, direccion_usuario=?, cp_usuario=?, poblacion_usuario=?, estado_provincia=?, pais_usuario=?, metodo_pago=?, tarjeta_usuario=?, caducidad_tarjeta=?, CCV=?, telefono_usuario=?, ruta_perfil=?, unidades_vendidas=?, unidades_compradas=?, NFT_comprado=?, NFT_vendido=? WHERE id_usuario=?");
                    $stmt->bind_param("ssssssssssssssissi", $nombre_usuario, $apellidos_usuario, $email_usuario, $direccion_usuario, $cp_usuario, $poblacion_usuario, $estado_provincia, $pais_usuario, $metodo_pago, $tarjeta_usuario, $caducidad_tarjeta, $CCV, $telefono_usuario, $ruta_perfil, $unidades_vendidas, $unidades_compradas, $NFT_comprado, $NFT_vendido, $id_usuario);
                
                    if ($stmt->execute()) {
                        echo "Datos actualizados correctamente.";
                    } else {
                        echo "Error al actualizar los datos: " . $stmt->error;
                    }
                
                    $stmt_ccv = $conexion->prepare("SELECT CCV FROM Usuarios WHERE id_usuario = ?");
                    $stmt_ccv->bind_param("i", $id_usuario);
                    $stmt_ccv->execute();
                    $result_ccv = $stmt_ccv->get_result();

                    if ($result_ccv->num_rows > 0) {
                        $row_ccv = $result_ccv->fetch_assoc();
                        $ccv_correcto = $row_ccv["CCV"];

                        if ($ccv_correcto == $CCV) {
                            $stmt = $conexion->prepare("UPDATE Usuarios SET nombre_usuario=?, apellidos_usuario=?, email_usuario=?, direccion_usuario=?, cp_usuario=?, poblacion_usuario=?, pais_usuario=?, metodo_pago=?, tarjeta_usuario=?, caducidad_tarjeta=?, CCV=? WHERE id_usuario=?");
                            $stmt->bind_param("sssssssssssi", $nombre_usuario, $apellidos_usuario, $email_usuario, $direccion_usuario, $cp_usuario, $poblacion_usuario, $pais_usuario, $metodo_pago, $tarjeta_usuario, $caducidad_tarjeta, $CCV, $id_usuario);

                            if ($stmt->execute()) {
                                echo "Datos actualizados correctamente.";
                            } else {
                                echo "Error al actualizar los datos: " . $stmt->error;
                            }
                        } else {
                            echo "El CCV introducido no coincide con el registrado en la base de datos.";
                        }
                    } else {
                        echo "Error al obtener el CCV de la base de datos.";
                    }

                    $stmt_ccv->close();
                    $stmt->close();
                }
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
                        echo "<label class='perfil-label' for='telefono_usuario'>Teléfono:</label>";
                        echo "<input class='perfil-input' type='text' id='telefono_usuario' name='telefono_usuario' value='" . $row["telefono_usuario"] . "' readonly>";
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
                        echo "<label class='perfil-label' for='estado_provincia'>Estado/Provincia:</label>";
                        echo "<input class='perfil-input' type='text' id='estado_provincia' name='estado_provincia' value='" . $row["estado_provincia"] . "' readonly>";
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
                        echo "<label class='perfil-label' for='CCV'>CCV:</label>";
                        echo "<input class='perfil-input' type='text' id='CCV' name='CCV' value='" . $row["CCV"] . "' readonly>";
                        echo "<button type='button' class='perfil-edit-button' onclick='editMetodosPago(\"metodos-pago\")'>Editar</button>";
                        echo "</fieldset>";
                        // Perfil
                        echo "<fieldset class='perfil-section' id='perfil'>";
                        echo "<legend>Perfil</legend>";
                        echo "<label class='perfil-label' for='ruta_perfil'>Ruta Perfil:</label>";
                        echo "<input class='perfil-input' type='text' id='ruta_perfil' name='ruta_perfil' value='" . $row["ruta_perfil"] . "' readonly>";
                        echo "</fieldset>";
                        // Unidades Vendidas y Compradas
                        echo "<fieldset class='perfil-section' id='unidades'>";
                        echo "<legend>Unidades</legend>";
                        echo "<label class='perfil-label' for='unidades_vendidas'>Unidades Vendidas:</label>";
                        echo "<input class='perfil-input' type='text' id='unidades_vendidas' name='unidades_vendidas' value='" . $row["unidades_vendidas"] . "' readonly>";
                        echo "<label class='perfil-label' for='unidades_compradas'>Unidades Compradas:</label>";
                        echo "<input class='perfil-input' type='text' id='unidades_compradas' name='unidades_compradas' value='" . $row["unidades_compradas"] . "' readonly>";
                        echo "</fieldset>";
                        // NFT
                        echo "<fieldset class='perfil-section' id='nft'>";
                        echo "<legend>NFT</legend>";
                        echo "<label class='perfil-label' for='NFT_comprado'>NFT Comprado:</label>";
                        echo "<input class='perfil-input' type='text' id='NFT_comprado' name='NFT_comprado' value='" . $row["NFT_comprado"] . "' readonly>";
                        echo "<label class='perfil-label' for='NFT_vendido'>NFT Vendido:</label>";
                        echo "<input class='perfil-input' type='text' id='NFT_vendido' name='NFT_vendido' value='" . $row["NFT_vendido"] . "' readonly>";
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
                section.classList.remove('edit-mode');
                inputs.forEach(function(input) {
                    input.setAttribute('readonly', true);
                    input.classList.remove('editable');
                });
            } else {
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
            
            function promptCCV() {
                return prompt("Por favor, introduzca el CCV para editar los datos:");
            }

            function validarCCV(ccv) {
                var ccvCorrecto = '<?php echo json_encode($row["CCV"]); ?>';
                return ccv === ccvCorrecto;
            }
            
            function mostrarMensajeError() {
                alert("CCV introducido no es correcto. Intentos restantes: " + (3 - intentos));
            }
            
            function handleEdit() {
                var ccv = promptCCV();
                if (ccv !== null) {
                    if (validarCCV(ccv)) {
                        tarjetaInput.value = '<?php echo $row["tarjeta_usuario"]; ?>';
                        toggleEdit(sectionId);
                    } else {
                        intentos++;
                        if (intentos < 3) {
                            mostrarMensajeError();
                            handleEdit();
                        } else {
                            window.location.href = "/";
                        }
                    }
                }
            }
            var intentos = 0;
            handleEdit();
        }
    </script>
</body>
</html>