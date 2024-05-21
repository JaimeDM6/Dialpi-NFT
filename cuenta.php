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

// Claves de cifrado (deberían estar en un archivo de configuración seguro)
define('CLAVE_CIFRADO', 'tarjeta_AES'); // Reemplaza con tu clave de cifrado segura
define('IV', '1234567890123456'); // Reemplaza con un IV seguro de 16 bytes

// Función para desencriptar datos

// Función para cifrar datos
function cifrar($data, $key) {
    return bin2hex(openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, IV));
}

$id_usuario = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
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
        } elseif (isset($_POST['modificar'])) {
            // Recoger y cifrar datos del formulario
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
            $titular_tarjeta = $_POST['titular_tarjeta'];
            $tarjeta_usuario = cifrar($_POST['tarjeta_usuario'], CLAVE_CIFRADO);
            $caducidad_tarjeta = cifrar($_POST['caducidad_tarjeta'], CLAVE_CIFRADO);
            $ccv = cifrar($_POST['CCV'], CLAVE_CIFRADO);


            
            // Actualizar datos del usuario
            $stmtupdate = $conexion->prepare("UPDATE Usuarios SET nombre_usuario=?, apellidos_usuario=?, email_usuario=?, password_usuario=?, tarjeta_usuario=?, caducidad_tarjeta=?, CCV=?, telefono_usuario=?, direccion_usuario=?, cp_usuario=?, poblacion_usuario=?, estado_provincia=?, pais_usuario=?, ruta_perfil=? WHERE id_usuario=?");
            $stmtupdate->bind_param("ssssssssssssssi", $nombre_usuario, $apellidos_usuario, $email_usuario, $password_usuario, $tarjeta_usuario, $caducidad_tarjeta, $ccv, $telefono_usuario, $direccion_usuario, $cp_usuario, $poblacion_usuario, $estado_provincia, $pais_usuario, $ruta_perfil, $id_usuario);

            if ($stmtupdate->execute()) {
                echo "Datos actualizados correctamente.";
            } else {
                echo "Error al actualizar los datos: " . $stmtupdate->error;
            }
            $stmtupdate->close();
        }
    } catch (Exception $e) {
        echo "Se ha producido un error: " . $e->getMessage();
    }
}

// Obtener datos del usuario para mostrar en el formulario
$stmt = $conexion->prepare("SELECT * FROM Usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $clave_cifrado = 'tarjeta_AES'; // Reemplaza con tu clave de cifrado segura
    $iv = '1234567890123456'; // Reemplaza con un IV seguro de 16 bytes
    
    $titular_tarjeta_descifrado = openssl_decrypt($row["titular_tarjeta"], 'aes-256-cbc', $clave_cifrado, OPENSSL_RAW_DATA, $iv);
    $tarjeta_usuario_descifrado = openssl_decrypt($row["tarjeta_usuario"], 'aes-256-cbc', $clave_cifrado, OPENSSL_RAW_DATA, $iv);
    $caducidad_tarjeta_descifrado = openssl_decrypt($row["caducidad_tarjeta"], 'aes-256-cbc', $clave_cifrado, OPENSSL_RAW_DATA, $iv);
    $ccv_descifrado = openssl_decrypt($row["CCV"], 'aes-256-cbc', $clave_cifrado, OPENSSL_RAW_DATA, $iv);
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mi cuenta - Dialpi NFT</title>
        
    </head>
    <body>
        <?php include 'header.php';
              include 'head.php'; ?>
        <main>
            <div class="perfil-container-left">
                <h2>Mi perfil</h2>
                <form id="perfil-form" class='perfil-form' method='post' action=''>
                    <input type='hidden' name='id_usuario' value='<?php echo $row["id_usuario"]; ?>'>
                    <!-- Información Personal -->
                    <fieldset class='perfil-section' id='informacion-personal'>
                        <legend>Información Personal</legend>
                        <label class='perfil-label' for='nombre_usuario'>Nombre:</label>
                        <input class='perfil-input' type='text' id='nombre_usuario' name='nombre_usuario' value='<?php echo $row["nombre_usuario"]; ?>' readonly>
                        <label class='perfil-label' for='apellidos_usuario'>Apellidos:</label>
                        <input class='perfil-input' type='text' id='apellidos_usuario' name='apellidos_usuario' value='<?php echo $row["apellidos_usuario"]; ?>' readonly>
                        <label class='perfil-label' for='email_usuario'>Email:</label>
                        <input class='perfil-input' type='email' id='email_usuario' name='email_usuario' value='<?php echo $row["email_usuario"]; ?>' readonly>
                        <label class='perfil-label' for='password_usuario'>Contraseña:</label>
                        <input class='perfil-input' type='password' id='password_usuario' name='password_usuario' value='<?php echo $row["password_usuario"]; ?>' readonly>
                        <label class='perfil-label' for='telefono_usuario'>Teléfono:</label>
                        <input class='perfil-input' type='text' id='telefono_usuario' name='telefono_usuario' value='<?php echo $row["telefono_usuario"]; ?>' readonly>
                    </fieldset>

                    <!-- Dirección -->
                    <fieldset class='perfil-section' id='direccion'>
                        <legend>Dirección</legend>
                        <label class='perfil-label' for='direccion_usuario'>Calle:</label>
                        <input class='perfil-input' type='text' id='direccion_usuario' name='direccion_usuario' value='<?php echo $row["direccion_usuario"]; ?>' readonly>
                        <label class='perfil-label' for='cp_usuario'>Código Postal:</label>
                        <input class='perfil-input' type='text' id='cp_usuario' name='cp_usuario' value='<?php echo $row["cp_usuario"]; ?>' readonly>
                        <label class='perfil-label' for='poblacion_usuario'>Población:</label>
                        <input class='perfil-input' type='text' id='poblacion_usuario' name='poblacion_usuario' value='<?php echo $row["poblacion_usuario"]; ?>' readonly>
                        <label class='perfil-label' for='estado_provincia'>Estado/Provincia:</label>
                        <input class='perfil-input' type='text' id='estado_provincia' name='estado_provincia' value='<?php echo $row["estado_provincia"]; ?>' readonly>
                        <label class='perfil-label' for='pais_usuario'>País:</label>
                        <input class='perfil-input' type='text' id='pais_usuario' name='pais_usuario' value='<?php echo $row["pais_usuario"]; ?>' readonly>
                    </fieldset>

                    <!-- Métodos de Pago -->
                    <fieldset class='perfil-section' id='metodos-pago'>
                        <legend>Métodos de Pago</legend>
                        <?php
                        
                         
                        $ultimos_cuatro_digitos = substr($tarjeta_usuario_descifrado, -4);
                        $tarjeta_mascara = '************ ' . $ultimos_cuatro_digitos;
                        ?>
                        <label class='perfil-label' for='titular_tarjeta'>Titular Tarjeta:</label>
                        <input class='perfil-input' type='text' id='titular_tarjeta' name='titular_tarjeta' value='<?php echo $titular_tarjeta_descifrado; ?>' readonly onclick='revelarTarjeta()'>
                        <label class='perfil-label' for='tarjeta_usuario'>Tarjeta de crédito:</label>
                        <input class='perfil-input' type='text' id='tarjeta_usuario' name='tarjeta_usuario' value='<?php echo $tarjeta_mascara; ?>' readonly onclick='revelarTarjeta()'>
                        <label class='perfil-label' for='caducidad_tarjeta'>Fecha de caducidad:</label>
                        <input class='perfil-input' type='text' id='caducidad_tarjeta' name='caducidad_tarjeta' value='<?php echo $caducidad_tarjeta_descifrado; ?>' readonly onclick='revelarTarjeta()'>
                        <label class='perfil-label' for='CCV'>CCV:</label> 
                        <input class='perfil-input' type='text' id='CCV' name='CCV' value='<?php echo $ccv_descifrado; ?>' readonly onclick='revelarTarjeta()'>
                    </fieldset>

                    <!-- Configuración -->
                    <fieldset class='perfil-section' id='configuracion'>
                        <legend>Configuración</legend>
                        <label class='perfil-label' for='ruta_perfil'>Foto de perfil:</label>
                        <input class='perfil-input' type='text' id='ruta_perfil' name='ruta_perfil' value='<?php echo $row["ruta_perfil"]; ?>' readonly>
                        <label class='perfil-label' for='aceptar_newsletter'>Aceptar newsletter:</label>
                        <input class='perfil-input' type='text' id='aceptar_newsletter' name='aceptar_newsletter' value='<?php echo $row["aceptar_newsletter"] ? "Sí" : "No"; ?>' readonly>
                    </fieldset>
                </form>
                <button id="editar-perfil" class='button__perfil__editar'>Editar perfil</button>
            </div>
        </main>
        <script src='/script/editarTarjeta.js'></script>
        <script src='/script/editarPerfil.js'></script>
        
        <?php include 'footer.php'; ?>
    </body>
    </html>

    <?php
} else {
    echo "No se encontraron datos del usuario.";
}
$stmt->close();
$conexion->close();
?>
