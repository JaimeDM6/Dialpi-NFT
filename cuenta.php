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

function desencriptar($data, $key) {
    $iv = '1234567890123456'; // Este es el IV que se debe usar con openssl
    return openssl_decrypt($data, 'aes-256-cbc', $key, 0, $iv);
}
?>
<main>
    <div class="perfil-container-left">
            <h2>Mi perfil</h2>

        <?php
        $id_usuario = $_SESSION['usuario']['id']; 

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_usuario = $_SESSION['usuario']['id']; 


            if (isset($_POST['confirmar_eliminar']) && $_POST['confirmar_eliminar'] == 'on') {
                // Eliminar cuenta
                $stmtdelete = $conexion->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
                $stmtdelete->bind_param("i", $id_usuario);

                if ($stmtdelete->execute()) {
                    echo "El usuario ha sido eliminado";
                    session_destroy();
                    header("Location: /login");
                    exit;
                } else {
                    echo "Error al eliminar la cuenta: " . $stmtdelete->error;
                }

                $stmtdelete->close();
            } else {
                if (isset($_POST['modificar'])) {
                $dni_usuario = $_POST['dni_usuario'];
                $nombre_usuario = $_POST['nombre_usuario'];
                $apellidos_usuario = $_POST['apellidos_usuario'];
                $email_usuario = $_POST['email_usuario'];
                $password_usuario = $_POST['password_usuario'];
                $telefono_usuario = $_POST['telefono_usuario'];
                $direccion_usuario = $_POST['direccion_usuario'];
                $cp_usuario = $_POST['cp_usuario'];
                $poblacion_usuario = $_POST['poblacion_usuario'];
                $estado_provincia = $_POST['estado_provincia'];
                $pais_usuario = $_POST['pais_usuario'];
                $ruta_perfil = $_POST['ruta_perfil'];

                // Obtener y cifrar datos de la tarjeta
                $tarjeta_usuario = $_POST['tarjeta_usuario'];
                $caducidad_tarjeta = $_POST['caducidad_tarjeta'];
                $ccv = $_POST['CCV'];

                // Cifrar los datos de la tarjeta
                $clave_cifrado = 'tarjeta_AES'; // Reemplaza con tu clave de cifrado segura
                $iv = '1234567890123456'; // Reemplaza con un IV seguro de 16 bytes

                $tarjeta_usuario_cifrada = bin2hex(openssl_encrypt($tarjeta_usuario, 'aes-256-cbc', $clave_cifrado, OPENSSL_RAW_DATA, $iv));
                $caducidad_tarjeta_cifrada = bin2hex(openssl_encrypt($caducidad_tarjeta, 'aes-256-cbc', $clave_cifrado, OPENSSL_RAW_DATA, $iv));
                $ccv_cifrado = bin2hex(openssl_encrypt($ccv, 'aes-256-cbc', $clave_cifrado, OPENSSL_RAW_DATA, $iv));

                // Preparar y ejecutar la consulta SQL para actualizar los datos
                $stmtupdate = $conexion->prepare("UPDATE Usuarios SET nombre_usuario=?, apellidos_usuario=?, email_usuario=?, password_usuario=?, tarjeta_usuario=?, caducidad_tarjeta=?, CCV=?, telefono_usuario=?, direccion_usuario=?, cp_usuario=?, poblacion_usuario=?, estado_provincia=?, pais_usuario=?, ruta_perfil=? WHERE id_usuario=?");
                $stmtupdate->bind_param("ssssssssssssssi", $nombre_usuario, $apellidos_usuario, $email_usuario, $password_usuario, $tarjeta_usuario_cifrada, $caducidad_tarjeta_cifrada, $ccv_cifrado, $telefono_usuario, $direccion_usuario, $cp_usuario, $poblacion_usuario, $estado_provincia, $pais_usuario, $ruta_perfil, $id_usuario);

                if ($stmtupdate->execute()) {
                    echo "Datos actualizados correctamente.";
                } else {
                    echo "Error al actualizar los datos: " . $stmtupdate->error;
                }

                $stmtupdate->close();
            }
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
                echo "<label class='perfil-label' for='id_usuario'>ID Cliente</label>";
                echo "<input class='perfil-input' type='text' id='id_usuario' name='id_usuario' value='" . $row["id_usuario"] . "' readonly>";
                echo "<label class='perfil-label' for='dni_usuario'>DNI</label>";
                echo "<input class='perfil-input' type='text' id='dni_usuario' name='dni_usuario' value='" . $row["dni_usuario"] . "' readonly>";
                echo "<label class='perfil-label' for='fecha_alta'>Fecha de alta:</label>";
                echo "<input class='perfil-input' type='text' id='fecha_alta' name='fecha_alta' value='" . $row["fecha_alta"] . "' readonly>";
                echo "<label class='perfil-label' for='nombre_usuario'>Nombre:</label>";
                echo "<input class='perfil-input' type='text' id='nombre_usuario' name='nombre_usuario' value='" . $row["nombre_usuario"] . "' >";
                echo "<label class='perfil-label' for='apellidos_usuario'>Apellidos:</label>";
                echo "<input class='perfil-input' type='text' id='apellidos_usuario' name='apellidos_usuario' value='" . $row["apellidos_usuario"] . "' >";
                echo "<label class='perfil-label' for='email_usuario'>Email:</label>";
                echo "<input class='perfil-input' type='email' id='email_usuario' name='email_usuario' value='" . $row["email_usuario"] . "' readonly>";
                echo "<label class='perfil-label' for='password_usuario'>Contraseña:</label>";
                echo "<input class='perfil-input' type='password' id='password_usuario' name='password_usuario' value='" . $row["password_usuario"] . "' readonly>";
                echo "<label class='perfil-label' for='telefono_usuario'>Teléfono:</label>";
                echo "<input class='perfil-input' type='text' id='telefono_usuario' name='telefono_usuario' value='" . $row["telefono_usuario"] . "' readonly>";
                echo "</fieldset>";

                // Dirección
                echo "<fieldset class='perfil-section' id='direccion'>";
                echo "<legend>Dirección</legend>";
                echo "<label class='perfil-label' for='direccion_usuario'>Calle:</label>";
                echo "<input class='perfil-input' type='text' id='direccion_usuario' name='direccion_usuario' value='" . $row["direccion_usuario"] . "' readonly>";
                echo "<label class='perfil-label' for='cp_usuario'>Código Postal:</label>";
                echo "<input class='perfil-input' type='text' id='cp_usuario' name='cp_usuario' value='" . $row["cp_usuario"] . "' readonly>";
                echo "<label class='perfil-label' for='poblacion_usuario'>Población:</label>";
                echo "<input class='perfil-input' type='text' id='poblacion_usuario' name='poblacion_usuario' value='" . $row["poblacion_usuario"] . "' readonly>";
                echo "<label class='perfil-label' for='estado_provincia'>Estado/Provincia:</label>";
                echo "<input class='perfil-input' type='text' id='estado_provincia' name='estado_provincia' value='" . $row["estado_provincia"] . "' readonly>";
                echo "<label class='perfil-label' for='pais_usuario'>País:</label>";
                echo "<input class='perfil-input' type='text' id='pais_usuario' name='pais_usuario' value='" . $row["pais_usuario"] . "' readonly>";
                echo "</fieldset>";

                // Métodos de Pago
                echo "<fieldset class='perfil-section' id='metodos-pago'>";
                echo "<legend>Métodos de Pago</legend>";
                $tarjeta_usuario = desencriptar($row["tarjeta_usuario"], 'tarjeta_AES');
                $ultimos_cuatro_digitos = substr($tarjeta_usuario, -4);
                echo "<label class='perfil-label' for='tarjeta_usuario'>Tarjeta:</label>";
                echo "<input class='perfil-input' type='text' id='tarjeta_usuario' name='tarjeta_usuario' value='************" . $ultimos_cuatro_digitos . "' readonly onclick='revelarTarjeta()'>";
                echo "<label class='perfil-label' for='caducidad_tarjeta'>Caducidad:</label>";
                echo "<input class='perfil-input' type='text' id='caducidad_tarjeta' name='caducidad_tarjeta' value='****' readonly onclick='revelarTarjeta()'>";
                echo "<label class='perfil-label' for='CCV'>CCV:</label>";
                echo "<input class='perfil-input' type='text' id='CCV' name='CCV' value='***' readonly onclick='revelarTarjeta()'>";
                echo "</fieldset>";

                // Perfil
                echo "<fieldset class='perfil-section' id='perfil'>";
                echo "<legend>Perfil</legend>";
                echo "<label class='perfil-label' for='ruta_perfil'>Ruta Perfil:</label>";
                echo "<input class='perfil-input' type='text' id='ruta_perfil' name='ruta_perfil' value='" . $row["ruta_perfil"] . "' readonly>";
                echo "</fieldset>";

                // Eliminar Cuenta
                echo "<fieldset class='perfil-section' id='eliminar-cuenta'>";
                echo "<legend>Eliminar Cuenta</legend>";
                echo "<label class='perfil-label' for='confirmar_eliminar'>Confirmar Eliminación de Cuenta:</label>";
                echo "<input class='perfil-checkbox' type='checkbox' id='confirmar_eliminar' name='confirmar_eliminar'>";
                echo "</fieldset>";
            
            echo "<button class='perfil-edit-button' type='button' name='editar' onclick='toggleEdit()'>Editar</button>";
            echo "<button class='perfil-save-button' type='submit' name='modificar' style='display:none;'>Guardar</button>";
            echo "</form>";
        }
            $stmt->close();
        } else {
            echo "No se encontraron resultados para el usuario actual.";
        }
        ?>
    </div>
</main>

<script>
        console.log("Script cargado correctamente");
        function toggleEdit() {
        var editButton = document.querySelector('.perfil-edit-button');
        var saveButton = document.querySelector('.perfil-save-button');
        var inputs = document.querySelectorAll('.perfil-input');

            if (editButton.innerText === 'Editar') {
                editButton.innerText = 'Cancelar';
                saveButton.style.display = 'inline';
                inputs.forEach(function(input) {
                    if (input.id !== 'id_usuario' && input.id !== 'dni_usuario' && input.id !== 'fecha_alta') {
                        input.removeAttribute('readonly');
                        input.classList.add('editable');
                    }
                });
            } else {
                editButton.innerText = 'Editar';
                saveButton.style.display = 'none';
                inputs.forEach(function(input) {
                    input.setAttribute('readonly', true);
                    input.classList.remove('editable');
                });
            }
        }

function revelarTarjeta() {
    var intentos = 0;
    function promptCCV() {
        return prompt("Por favor, introduzca el CCV para editar los datos:");
    }

    function validarCCV(ccv) {
        var ccvCorrecto = '<?php echo json_encode(desencriptar($row["CCV"])); ?>';
        return ccv === ccvCorrecto;
    }

    function mostrarMensajeError() {
        alert("CCV introducido no es correcto. Intentos restantes: " + (3 - intentos));
    }

    function handleEdit() {
        var ccv = promptCCV();
        if (ccv !== null) {
            if (validarCCV(ccv)) {
                document.getElementById('tarjeta_usuario').value = '<?php echo desencriptar($row["tarjeta_usuario"]); ?>';
                document.getElementById('caducidad_tarjeta').value = '<?php echo desencriptar($row["caducidad_tarjeta"]); ?>';
                document.getElementById('CCV').value = '<?php echo desencriptar($row["CCV"]); ?>';
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
    handleEdit();
}
</script>
<script src="script/script.js"></script>
<?php
include 'footer.php';
?>
</body>
</html>
