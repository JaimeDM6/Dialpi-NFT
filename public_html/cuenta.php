<?php
$title = 'Mi cuenta - Dialpi NFT';
session_start();
ob_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['usuario'])) {
    header("Location: /login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarDireccion'])) {
    $idUsuario = $_SESSION['usuario']['id'];
    $query = "UPDATE Usuarios SET direccion_usuario = NULL, cp_usuario = NULL, poblacion_usuario = NULL, estado_provincia = NULL, pais_usuario = NULL 
              WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    echo json_encode(['success' => $stmt->affected_rows > 0]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar-passwd'])) {
    $response = array('error' => false, 'message' => '');

    $usuario_id = $_SESSION['usuario']['id'];
    $password = $_POST["current-password"];
    $new_password = $_POST["new-password"];
    $confirm_password = $_POST["confirm-password"];

    $sql = "SELECT password_usuario FROM Usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && (password_verify($password, $user['password_usuario']) || hash('sha512', $password) === $user['password_usuario'])) {
        if ($new_password !== $confirm_password) {
            $response['error'] = true;
            $response['message'] = "Las contraseñas no coinciden.";
        } else {
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE Usuarios SET password_usuario = ? WHERE id_usuario = ?";
            $update_stmt = $conexion->prepare($update_sql);
            $update_stmt->bind_param("si", $new_password_hashed, $usuario_id);
            if ($update_stmt->execute()) {
                $response['message'] = 'success';
            } else {
                $response['error'] = true;
                $response['message'] = " Error al actualizar la contraseña.";
            }
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Contraseña incorrecta.";
    }
    
    echo json_encode($response);
    exit;
}

$parametro = array_key_first($_GET) ? array_key_first($_GET) : 'perfil';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Amaranth" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/grover-heavy" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://kit.fontawesome.com/ea577ecbca.js" crossOrigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main>
        <div class="container-cuenta">
            <div class="menu-columna">
                <ul>
                    <li class="<?= $parametro == 'perfil' || $parametro == 'perfil-edit' ? 'active' : '' ?>"><a href="/cuenta?perfil">Perfil</a></li>
                    <?php if (!isset($_SESSION['administrador']) || $_SESSION['administrador'] !== true): ?>
                        <li class="<?= $parametro == 'direccion' || $parametro == 'direccion-edit' ? 'active' : '' ?>"><a href="/cuenta?direccion">Dirección</a></li>
                    <?php endif; ?>
                    <li class="<?= $parametro == 'seguridad' ? 'active' : '' ?>"><a href="/cuenta?seguridad">Seguridad</a></li>
                    <?php if (!isset($_SESSION['administrador']) || $_SESSION['administrador'] !== true): ?>
                        <li class="<?= $parametro == 'metodo-pago' ? 'active' : '' ?>"><a href="/cuenta?metodo-pago">Método de pago</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="contenido">
                <?php
                switch ($parametro) {
                    case 'perfil':
                        $id_usuario = $_SESSION['usuario']['id'];

                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
                            $query = "SELECT id_pedido FROM Pedidos_NFT WHERE id_usuario = ?";
                            $stmt = $conexion->prepare($query);
                            $stmt->bind_param("i", $id_usuario);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $pedidos = $result->fetch_all(MYSQLI_ASSOC);
                            $stmt->close();
                        
                            foreach ($pedidos as $pedido) {
                                $query = "DELETE FROM Detalle_Pedido WHERE id_pedido = ?";
                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param("i", $pedido['id_pedido']);
                                $stmt->execute();
                                $stmt->close();
                            }
                        
                            $query = "DELETE FROM Pedidos_NFT WHERE id_usuario = ?";
                            $stmt = $conexion->prepare($query);
                            $stmt->bind_param("i", $id_usuario);
                            $stmt->execute();
                            $stmt->close();
                        
                            $query = "DELETE FROM Usuarios WHERE id_usuario = ?";
                            $stmt = $conexion->prepare($query);
                            $stmt->bind_param("i", $id_usuario);
                            $stmt->execute();
                            $stmt->close();
                        }

                        $query = "SELECT dni_usuario, nombre_usuario, apellidos_usuario, email_usuario, telefono_usuario 
                                        FROM Usuarios WHERE id_usuario = ?";
                        
                        $stmt = $conexion->prepare($query);
                        $stmt->bind_param("i", $id_usuario);
                        $stmt->execute();
                        $stmt->bind_result($dni, $nombre, $apellidos, $correo, $telefono);
                        $stmt->fetch();
                        $stmt->close();
                        
                            echo '<div class="perfil">';
                            echo '<div class="foto-nombre">';
                            echo '<div id="overlay" class="overlay"></div>';
                            echo '<div class="perfil-img-wrapper">';
                            echo '<img class="perfil-img" src="/images.php?token_foto=' . $_SESSION['usuario']['token_foto'] . '" id="uploaded_image" alt="Imagen de perfil">';
                            echo '<div class="perfil-img-text">Cambiar</div>';
                            echo '<input type="file" name="image" class="image" id="upload_image" accept="image/*" style="display:none">';
                            echo '</div>';
                            echo '<h1>' . $nombre . ' ' . $apellidos . '</h1>';
                            echo '</div>';
                            ?>
                            <div class="modal-foto" id="modal-foto">
                                <div class="modal-content">
                                    <div class="recortar-imagen">
                                        <h3>Recortar Imagen</h3>
                                        <span class="close">&times;</span>
                                    </div>
                                    <div class="img-container">
                                        <img id="sample_image" src="" alt="Sample Image">
                                    </div>
                                    <div class="button-container">
                                        <button id="crop">Recortar</button>
                                        <button id="cancel">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                            <?php
                            echo '<div class="perfil-centrado">';
                            echo '<p><strong>DNI:</strong> ' . $dni . '</p>';
                            echo '<p><strong>Correo electrónico:</strong> ' . $correo . '</p>';
                            echo '<p><strong>Teléfono:</strong> ' . $telefono . '</p>';
                            echo '</div>';
                            
                            echo '<div class="botones-centrados">';
                            echo '<button onclick="location.href=\'/cuenta?perfil-edit\'">Editar</button>';
                            echo '<button id="deleteAccount" data-user-id="' . $_SESSION['usuario']['id'] . '">Eliminar cuenta</button>';
                            echo '</div>';
                            echo '</div>';
                            break;
                        case 'perfil-edit':
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $dni = $_POST['dni_usuario'];
                                $nombre = $_POST['nombre_usuario'];
                                $apellidos = $_POST['apellidos_usuario'];
                                $correo = $_POST['email_usuario'];
                                $telefono = $_POST['telefono_usuario'];

                                $query = "UPDATE Usuarios 
                                            SET dni_usuario = ?, nombre_usuario = ?, apellidos_usuario = ?, email_usuario = ?, telefono_usuario = ? 
                                            WHERE id_usuario = ?";

                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('sssssi', $dni, $nombre, $apellidos, $correo, $telefono, $_SESSION['usuario']['id']);
                                $stmt->execute();

                                if ($stmt->affected_rows > 0) {
                                    header('Location: /cuenta?perfil');
                                } else {
                                    header('Location: /cuenta?perfil');
                                }

                                $stmt->close();
                            }
                            
                            $idUsuario = $_SESSION['usuario']['id'];
                            $query = "SELECT dni_usuario, nombre_usuario, apellidos_usuario, email_usuario, telefono_usuario  FROM Usuarios WHERE id_usuario = ?";
                            $stmt = $conexion->prepare($query);
                            $stmt->bind_param('i', $idUsuario);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $perfil = $result->fetch_assoc();
                            ?>
                            <form action="/cuenta?perfil-edit" method="post" class="perfil-form">
                                <h3>Editar perfil</h3>
                                <label for="dni_usuario">DNI:</label>
                                <input type="text" id="dni_usuario" name="dni_usuario" value="<?= $perfil['dni_usuario'] ?>">
                                <label for="nombre_usuario">Nombre:</label>
                                <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?= $perfil['nombre_usuario'] ?>">
                                <label for="apellidos_usuario">Apellidos:</label>
                                <input type="text" id="apellidos_usuario" name="apellidos_usuario" value="<?= $perfil['apellidos_usuario'] ?>">
                                <label for="email_usuario">Correo electronico:</label>
                                <input type="text" id="email_usuario" name="email_usuario" value="<?= $perfil['email_usuario'] ?>">
                                <label for="telefono_usuario">Telefono:</label>
                                <input type="text" id="telefono_usuario" name="telefono_usuario" value="<?= $perfil['telefono_usuario'] ?>">
                                <br>
                                <div class="perfil-button">
                                    <input type="submit" value="Guardar">
                                    <a href="/cuenta?perfil" class="cancel-button">Cancelar</a>
                                </div>
                            </form>
                            <?php
                            break;
                    case 'direccion':
                        if (!isset($_SESSION['administrador']) || $_SESSION['administrador'] !== true) {
                            $idUsuario = $_SESSION['usuario']['id'];
                        
                            $query = "SELECT direccion_usuario, cp_usuario, poblacion_usuario, estado_provincia, pais_usuario 
                                        FROM Usuarios WHERE id_usuario = ?";
                            $stmt = $conexion->prepare($query);
                            $stmt->bind_param('i', $idUsuario);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $direccion = $result->fetch_assoc();

                            if ($direccion['direccion_usuario'] !== null) {
                            ?>
                                <div class="direccion">
                                    <h3>Dirección de facturación</h3>
                                    <p>Dirección: <?= $direccion['direccion_usuario'] ?></p>
                                    <p>Código Postal: <?= $direccion['cp_usuario'] ?></p>
                                    <p>Población: <?= $direccion['poblacion_usuario'] ?></p>
                                    <p>Estado/Provincia: <?= $direccion['estado_provincia'] ?></p>
                                    <p>País: <?= $direccion['pais_usuario'] ?></p><br>
                                    <div class="botones-centrados">
                                        <button onclick="location.href='/cuenta?direccion-edit'">Editar</button>
                                        <button id="eliminar-direccion">Eliminar</button>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <div class="direccion">
                                    <p>No hay ninguna dirección guardada.</p><br>
                                    <div class="botones-centrados">
                                        <button onclick="location.href='/cuenta?direccion-edit'">Añadir</button>
                                    </div>
                                </div>
                            <?php
                            }
                        } else {
                            header('Location: /cuenta?perfil');
                        }
                        break;
                    case 'direccion-edit':
                        if (!isset($_SESSION['administrador']) || $_SESSION['administrador'] !== true) {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $direccion = $_POST['direccion'];
                                $cp = $_POST['cp'];
                                $poblacion = $_POST['poblacion'];
                                $estado = $_POST['estado'];
                                $pais = $_POST['pais'];

                                $query = "UPDATE Usuarios 
                                            SET direccion_usuario = ?, cp_usuario = ?, poblacion_usuario = ?, estado_provincia = ?, pais_usuario = ? 
                                            WHERE id_usuario = ?";

                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('sssssi', $direccion, $cp, $poblacion, $estado, $pais, $_SESSION['usuario']['id']);
                                $stmt->execute();

                                if ($stmt->affected_rows > 0) {
                                    header('Location: /cuenta?direccion');
                                } else {
                                    header('Location: /cuenta?direccion');
                                }

                                $stmt->close();
                            }
                            
                            $idUsuario = $_SESSION['usuario']['id'];
                            $query = "SELECT direccion_usuario, cp_usuario, poblacion_usuario, estado_provincia, pais_usuario FROM Usuarios WHERE id_usuario = ?";
                            $stmt = $conexion->prepare($query);
                            $stmt->bind_param('i', $idUsuario);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $direccion = $result->fetch_assoc();
                            ?>
                            <form action="/cuenta?direccion-edit" method="post" class="direccion-form">
                                <h3>Dirección de facturación</h3>
                                <label for="direccion">Dirección:</label>
                                <input type="text" id="direccion" name="direccion" value="<?= $direccion['direccion_usuario'] ?>">
                                <label for="cp">Código Postal:</label>
                                <input type="text" id="cp" name="cp" value="<?= $direccion['cp_usuario'] ?>">
                                <label for="poblacion">Población:</label>
                                <input type="text" id="poblacion" name="poblacion" value="<?= $direccion['poblacion_usuario'] ?>">
                                <label for="estado">Estado/Provincia:</label>
                                <input type="text" id="estado" name="estado" value="<?= $direccion['estado_provincia'] ?>">
                                <label for="pais">País:</label>
                                <?php include 'select_paises.php'; ?>
                                <br>
                                <div class="direccion-button">
                                    <input type="submit" value="Guardar">
                                    <a href="/cuenta?direccion" class="cancel-button">Cancelar</a>
                                </div>
                            </form>
                            <?php
                        } else {
                            header('Location: /cuenta?perfil');
                        }
                        break;
                    case 'seguridad':
                        ?>
                        <div class="seguridad">
                            <form method="POST" action="/cuenta" class="login-form" name="cambiar-passwd">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'])?>">
                                <input type="hidden" name="cambiar-passwd" value="1">
                                <p id="error-message" style="color: red;"></p>
                                <label for="current-password">Contraseña actual:</label><br>
                                <div class="password-container">
                                    <input type="password" id="current-password" name="current-password" required autocomplete="off">
                                    <i class="fas fa-eye" id="toggle-current-password"></i>
                                </div>
                                
                                <label for="new-password">Nueva contraseña:</label><br>
                                <div class="password-container">
                                    <input type="password" id="new-password" name="new-password" required autocomplete="new-password">
                                    <i class="fas fa-eye" id="toggle-new-password"></i>
                                </div>
                                
                                <label for="confirm-password">Confirmar contraseña:</label><br>
                                <div class="password-container">
                                    <input type="password" id="confirm-password" name="confirm-password" required autocomplete="new-password">
                                    <i class="fas fa-eye" id="toggle-confirm-password"></i>
                                </div>
                                <input type="submit" value="Cambiar contraseña">
                            </form>
                        </div>
                        <?php
                        break;
                    case 'metodo-pago':
                        if (!isset($_SESSION['administrador']) || $_SESSION['administrador'] !== true) {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
                                $usuario_id = $_SESSION['usuario']['id'];
                                $query = "UPDATE Usuarios SET tarjeta_usuario = NULL, caducidad_tarjeta = NULL, CCV = NULL WHERE id_usuario = ?";
                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param("i", $usuario_id);
                                $stmt->execute();
                                $stmt->close();
                            }
                        
                            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form-modificar'])) {
                                $titular_tarjeta = $_POST['titular-tarjeta'];
                                $tarjeta_usuario = $_POST['numero-tarjeta'];
                                $mes = $_POST['mes'];
                                $year = $_POST['year'];
                                $caducidad_tarjeta = $mes . '/' . $year;
                                $CCV = $_POST['ccv'];
                                $id = $_SESSION['usuario']['id'];
                        
                                $stmt = $conexion->prepare("UPDATE Usuarios SET titular_tarjeta = AES_ENCRYPT(?, 'tarjeta_AES'), tarjeta_usuario = AES_ENCRYPT(?, 'tarjeta_AES'), caducidad_tarjeta = AES_ENCRYPT(?, 'tarjeta_AES'), CCV = AES_ENCRYPT(?, 'tarjeta_AES') WHERE id_usuario = ?");
                                $stmt->bind_param("ssssi", $titular_tarjeta, $tarjeta_usuario, $caducidad_tarjeta, $CCV, $id);
                                $stmt->execute();
                            }
                        
                            
                            ?>
                            <div class="metodo-pago">
                                <?php
                                if (isset($_SESSION['usuario']['id'])) {
                                    function getCardType($cardNumber) {
                                        $cardTypes = array(
                                            "amex" => array("/^3[47][0-9]{13}$/"),
                                            "visa" => array("/^4[0-9]{12}(?:[0-9]{3})?$/"),
                                            "mastercard" => array("/^5[1-5][0-9]{14}$/", "/^2[2-7][0-9]{14}$/"),
                                            "discover" => array("/^6011[0-9]{12}[0-9]*$/", "/^62[24568][0-9]{13}[0-9]*$/", "/^6[45][0-9]{14}[0-9]*$/"),
                                            "diners" => array("/^3[0689][0-9]{12}[0-9]*$/"),
                                            "jcb" => array("/^35[0-9]{14}[0-9]*$/")
                                        );
                                
                                        foreach ($cardTypes as $type => $regexes) {
                                            foreach ($regexes as $regex) {
                                                if (preg_match($regex, str_replace(' ', '', $cardNumber))) {
                                                    return $type;
                                                }
                                            }
                                        }
                                
                                        return "card";
                                    }
                                    
                                    $usuario_id = $_SESSION['usuario']['id'];
                                    $query = "SELECT CAST(AES_DECRYPT(titular_tarjeta, 'tarjeta_AES') AS CHAR) as titular_tarjeta, CAST(AES_DECRYPT(tarjeta_usuario, 'tarjeta_AES') AS CHAR) as tarjeta_usuario, CAST(AES_DECRYPT(caducidad_tarjeta, 'tarjeta_AES') AS CHAR) as caducidad_tarjeta, CAST(AES_DECRYPT(CCV, 'tarjeta_AES') AS CHAR) as CCV FROM Usuarios WHERE id_usuario = ?";
                                    $stmt = $conexion->prepare($query);
                                    $stmt->bind_param("i", $usuario_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    while ($tarjeta = $result->fetch_assoc()) {
                                        if ($tarjeta['tarjeta_usuario'] !== NULL) {
                                            $numeroTarjeta = $tarjeta['tarjeta_usuario'];
                                            $tipoTarjeta = getCardType($numeroTarjeta);
                                            $logo = "/img/card/" . $tipoTarjeta . ".png";
                                            $caducidadTarjeta = $tarjeta['caducidad_tarjeta'];
                                            $fechaCaducidad = DateTime::createFromFormat('m/y', $caducidadTarjeta);
                                            $fechaActual = new DateTime();
                                            $tarjetaCaducada = $fechaCaducidad < $fechaActual;
                                            
                                            echo "<form class='tarjeta-credito' method='post'>";
                                            echo "<h3 class='title'>Método de pago</h3>";
                                            echo "<div class='tarjeta-guardada'>";
                                            echo "<img src='$logo' alt='Logo de $tipoTarjeta' />";
                                            echo "<p>Tarjeta que termina en ****" . substr($numeroTarjeta, -4) . "</p>";
                                            
                                            if ($tarjetaCaducada) {
                                                echo "<p style='color: red;'>Fecha de caducidad: " . $caducidadTarjeta . "   Tarjeta caducada</p>";
                                            } else {
                                                echo "<p>Fecha de caducidad: " . $caducidadTarjeta . "</p>";
                                            }
                                            
                                            echo "<button class='boton-modificar' data-ccv='{$tarjeta['CCV']}'>Modificar</button>";
                                            echo "<button class='boton-eliminar'>Eliminar</button>";
                                            echo "</div>";
                                            echo "</form>";
                                            ?>
                                            <div id="ModalTarjeta" class="modal">
                                                <div class="modal-content">
                                                    <div class="modificar-tarjeta">
                                                        <h3>Modificar tarjeta</h3>
                                                        <span class="close">&times;</span>
                                                    </div>
                                                    <form class="form-modificar" action="/checkout?metodo-pago" method="post">
                                                        <input type="text" name="titular-tarjeta" class="titular-tarjeta" placeholder="Titular de la tarjeta" autocomplete="cc-name" value="<?php echo $tarjeta['titular_tarjeta']; ?>" required>
                                                        <input type="text" name="numero-tarjeta" class="numero-tarjeta" placeholder="Número de tarjeta" autocomplete="cc-number" value="<?php echo $tarjeta['tarjeta_usuario']; ?>" maxlength="19" required>
                                                        <div class="fecha-cvv">
                                                            <div class="mes-tarjeta">
                                                                <select name="mes" autocomplete="cc-exp-month" required>
                                                                    <?php
                                                                    $caducidad = explode('/', $tarjeta['caducidad_tarjeta']);
                                                                    $mesActual = $caducidad[0];
                                                                    $meses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
                                                                    for ($i = 1; $i <= 12; $i++) {
                                                                        $mes = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                                        $nombreMes = $meses[$i - 1];
                                                                        $selected = $mes === $mesActual ? 'selected' : '';
                                                                        echo "<option value='$mes' $selected>$nombreMes</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="year-tarjeta">
                                                                <select name="year" autocomplete="cc-exp-year" required>
                                                                    <?php
                                                                    $yearActual = '20' . $caducidad[1];
                                                                    $yearInicio = date('Y');
                                                                    for ($i = $yearInicio; $i <= $yearInicio + 15; $i++) {
                                                                        $valor = substr($i, -2);
                                                                        $selected = $i === (int)$yearActual ? 'selected' : '';
                                                                        echo "<option value='$valor' $selected>$i</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="cvv">
                                                                <input type="text" name="ccv" class="cvv-input" placeholder="CVV" autocomplete="cc-csc" maxlength="3" value="<?php echo $tarjeta['CCV']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="metodo-pago-botones">
                                                            <button type="submit" class="boton-proceder" name="form-modificar">Guardar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php
                                            $stmt->close();
                                        } else {
                                            ?>
                                            <form class="tarjeta-credito" action="/procesar-pago" method="post">
                                                <h3 class="title">Método de pago</h3>
                                                <input type="text" name="titular-tarjeta" class="titular-tarjeta" placeholder="Titular de la tarjeta" autocomplete="cc-name" required>
                                                <input type="text" name="numero-tarjeta" class="numero-tarjeta" placeholder="Número de tarjeta" autocomplete="cc-number" maxlength="19" required>
                                                <div class="fecha-cvv">
                                                    <div class="mes-tarjeta">
                                                        <select name="mes" autocomplete="cc-exp-month" required>
                                                            <?php
                                                            $meses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
                                                            for ($i = 1; $i <= 12; $i++) {
                                                                $mes = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                                $nombreMes = $meses[$i - 1];
                                                                echo "<option value='$mes'>$nombreMes</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="year-tarjeta">
                                                        <select name="year" autocomplete="cc-exp-year" required>
                                                            <?php
                                                            $yearInicio = date('Y');
                                                            for ($i = $yearInicio; $i <= $yearInicio + 15; $i++) {
                                                                $valor = substr($i, -2);
                                                                echo "<option value='$valor'>$i</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="cvv">
                                                        <input type="text" name="ccv" class="cvv-input" placeholder="CVV" autocomplete="cc-csc" maxlength="3" required>
                                                    </div>
                                                </div>
                                                <div class="metodo-pago-botones">
                                                    <button type="submit" class="boton-proceder" name="boton-proceder">Guardar tarjeta</button>
                                                </div>
                                            </form>
                                        <?php
                                        }
                                    }
                                }
                        } else {
                            header('Location: /cuenta?perfil');
                        }
                        break;
                }
                ?>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="script/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const perfilImgWrapper = document.querySelector('.perfil-img-wrapper');
            const uploadImage = document.getElementById('upload_image');
            const modalFoto = document.getElementById('modal-foto');
            const closeModal = document.querySelector('.modal-content .close');
            const sampleImage = document.getElementById('sample_image');
            const cropButton = document.getElementById('crop');
            const cancelButton = document.getElementById('cancel');
            let cropper;

            perfilImgWrapper.addEventListener('click', () => {
                uploadImage.click();
            });

            uploadImage.addEventListener('change', (event) => {
                const files = event.target.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    const maxFileSize = 2 * 1024 * 1024;

                    if (file.size > maxFileSize) {
                        alert('El archivo es demasiado grande. El tamaño máximo permitido es de 2MB.');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        sampleImage.src = event.target.result;
                        sampleImage.onload = function() {
                            const imgWidth = sampleImage.naturalWidth;
                            const imgHeight = sampleImage.naturalHeight;
                            const container = document.querySelector('.img-container');

                            if (imgWidth > imgHeight) {
                                container.style.width = '20em';
                                container.style.height = 'auto';
                            } else {
                                container.style.width = 'auto';
                                container.style.height = '20em';
                            }

                            modalFoto.style.display = 'flex';
                            if (cropper) {
                                cropper.destroy();
                            }
                            cropper = new Cropper(sampleImage, {
                                aspectRatio: 1,
                                viewMode: 1,
                                autoCropArea: 1,
                                responsive: true,
                                background: false,
                                center: true
                            });
                        };
                    };
                    reader.readAsDataURL(file);
                }
            });

            closeModal.addEventListener('click', () => {
                modalFoto.style.display = 'none';
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            });

            cancelButton.addEventListener('click', () => {
                modalFoto.style.display = 'none';
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            });

            cropButton.addEventListener('click', () => {
                cropButton.classList.add('loading');
                cropButton.disabled = true;
                cropButton.textContent = 'Subiendo';

                const canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400,
                });

                canvas.toBlob((blob) => {
                    const url = URL.createObjectURL(blob);
                    const formData = new FormData();
                    formData.append('croppedImage', blob, 'profile.png');

                    fetch('/upload', {
                        method: 'POST',
                        body: formData,
                    }).then((response) => {
                        return response.json();
                    }).then((data) => {
                        cropButton.classList.remove('loading');
                        cropButton.disabled = false;
                        cropButton.textContent = 'Recortar';

                        if (data.status === 'success') {
                            modalFoto.style.display = 'none';
                            cropper.destroy();
                            cropper = null;
                            document.getElementById('uploaded_image').src = url;
                            window.location.reload();
                        } else {
                            alert(data.message);
                            console.error(data.message);
                        }
                    }).catch((error) => {
                        cropButton.classList.remove('loading');
                        cropButton.disabled = false;
                        cropButton.textContent = 'Recortar';
                        alert('Error de red. Por favor, inténtelo de nuevo.');
                        console.error(error);
                    });
                }, 'image/png');
            });
        });

        document.querySelectorAll('.boton-eliminar').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                var confirmation = confirm('¿Estás seguro/a de que quieres eliminar esta tarjeta?');
                if (confirmation) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '/cuenta?metodo-pago', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send('eliminar');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            alert('Tarjeta eliminada con éxito.');
                            location.reload();
                        } else {
                            alert('Hubo un error al eliminar la tarjeta.');
                        }
                    };
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var numeroTarjeta = document.querySelector('.numero-tarjeta');
            var cvv = document.querySelector('.cvv-input');

            if (cvv) {
                cvv.addEventListener('input', function() {
                    var valor = this.value.replace(/\D/g, '');
                    this.value = valor;
                });
            }

            if (numeroTarjeta) {
                numeroTarjeta.addEventListener('input', updateCardInfo);
            }

            function updateCardInfo() {
                var cardNumber = this.value.replace(/\D/g, '');
                var cardType = getCardType(cardNumber);

                switch (cardType) {
                    case 'amex':
                        cardNumber = cardNumber.replace(/(\d{4})(\d{6})(\d{5})/, '$1 $2 $3');
                        cvv.setAttribute('maxlength', '4');
                        break;
                    case 'visa':
                    case 'mastercard':
                    case 'discover':
                        cardNumber = cardNumber.replace(/(\d{4})/g, '$1 ').trim();
                        cvv.setAttribute('maxlength', '3');
                        break;
                    case 'diners':
                        cardNumber = cardNumber.replace(/(\d{4})(\d{4})(\d{4})(\d{2})/, '$1 $2 $3 $4');
                        cvv.setAttribute('maxlength', '3');
                        break;
                    case 'jcb':
                        cardNumber = cardNumber.replace(/(\d{4})/g, '$1 ').trim();
                        cvv.setAttribute('maxlength', '3');
                        break;
                    default:
                        cardNumber = cardNumber.replace(/(\d{4})/g, '$1 ').trim();
                        cvv.setAttribute('maxlength', '3');
                        break;
                }

                this.value = cardNumber;

                if (cardType) {
                    this.style.backgroundImage = 'url(/img/card/' + cardType + '.png)';
                    this.style.backgroundRepeat = 'no-repeat';
                    this.style.backgroundPosition = 'right 1.2em center';
                    this.style.backgroundSize = 'auto 1.2em';
                } else {
                    this.style.backgroundImage = '';
                }
            }

            function getCardType(cardNumber) {
                var cardTypes = {
                    amex: [/^3[47][0-9]{13}$/],
                    visa: [/^4[0-9]{12}(?:[0-9]{3})?$/],
                    mastercard: [/^5[1-5][0-9]{14}$/, /^2[2-7][0-9]{14}$/],
                    discover: [/^6011[0-9]{12}[0-9]*$/, /^62[24568][0-9]{13}[0-9]*$/, /^6[45][0-9]{14}[0-9]*$/],
                    diners: [/^3[0689][0-9]{12}[0-9]*$/],
                    jcb: [/^35[0-9]{14}[0-9]*$/]
                };

                for (var type in cardTypes) {
                    var regexes = cardTypes[type];
                    for (var i = 0; i < regexes.length; i++) {
                        if (regexes[i].test(cardNumber.replace(/\s/g, ''))) {
                            return type;
                        }
                    }
                }

                return null;
            }

            document.querySelectorAll('.boton-modificar').forEach(function(button, index) {
                var attempts = localStorage.getItem('attempts' + index) || 0;
                if (localStorage.getItem('blocked' + index) === 'true') {
                    attempts = 4;
                }
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (attempts >= 3) {
                        alert('Has alcanzado el límite de intentos. No puedes modificar esta tarjeta.');
                        return;
                    }
                    var ccv = prompt('Por favor, introduce el CCV de la tarjeta:');
                    if (ccv === null) {
                        return;
                    }

                    if (ccv !== button.dataset.ccv) {
                        attempts++;
                        localStorage.setItem('attempts' + index, attempts);
                        if (attempts >= 3) {
                            alert('Has alcanzado el límite de intentos. No puedes modificar esta tarjeta.');
                            localStorage.setItem('blocked' + index, 'true');
                        } else if (attempts < 3) {
                            alert('CCV incorrecto. Te quedan ' + (3 - attempts) + ' intentos.');
                        }
                    } else {
                        attempts = 0;
                        var modal = document.getElementById("ModalTarjeta");
                        var span = document.getElementsByClassName("close")[0];

                        var numeroTarjetaInput = document.querySelector('.numero-tarjeta');
                        updateCardInfo.call(numeroTarjetaInput);

                        modal.style.display = "flex";

                        span.onclick = function() {
                            modal.style.display = "none";
                        }

                        window.onclick = function(event) {
                            if (event.target == modal) {
                                modal.style.display = "none";
                            }
                        }

                        document.querySelector('.form-modificar').addEventListener('submit', function(event) {
                            document.getElementById('form-modificar').submit();
                            document.getElementById('ModalTarjeta').style.display = 'none';
                        });
                    }
                });
            });
        });

        $(document).ready(function() {
            $('.login-form').on('submit', function(event) {
                event.preventDefault();

                $.ajax({
                    url: '/cuenta?seguridad',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            $('#error-message').text(response.message);
                        } else {
                            alert('La contraseña se ha cambiado exitosamente.');
                            window.location.href = '/cuenta';
                        }
                    }
                });
            });
        
            $('#toggle-current-password').click(function() {
                let currentPasswordInput = $('#current-password');
                let currentPasswordType = currentPasswordInput.attr('type');

                if (currentPasswordType === 'password') {
                    currentPasswordInput.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    currentPasswordInput.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#toggle-new-password').click(function() {
                let newPasswordInput = $('#new-password');
                let newPasswordType = newPasswordInput.attr('type');

                if (newPasswordType === 'password') {
                    newPasswordInput.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    newPasswordInput.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#toggle-confirm-password').click(function() {
                let confirmPasswordInput = $('#confirm-password');
                let confirmPasswordType = confirmPasswordInput.attr('type');

                if (confirmPasswordType === 'password') {
                    confirmPasswordInput.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    confirmPasswordInput.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#eliminar-direccion').on('click', function(e) {
                e.preventDefault();
            
                $.ajax({
                    url: '/cuenta?direccion',
                    method: 'POST',
                    data: { eliminarDireccion: true },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('.direccion').html('<p>No hay ninguna dirección guardada.</p><br><div class="botones-centrados"><button onclick="location.href=\'/cuenta?direccion-edit\'">Añadir</button></div>');
                        } else {
                            alert('Hubo un error al eliminar la dirección.');
                        }
                    }
                });
            });
        });

        document.getElementById('deleteAccount').addEventListener('click', function() {
            var userId = this.getAttribute('data-user-id');
        
            if (confirm('¿Estás seguro/a de que quieres eliminar la cuenta? Esta acción no es reversible.')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/cuenta?perfil', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        var logoutXhr = new XMLHttpRequest();
                        logoutXhr.open('POST', '/logout', true);
                        logoutXhr.onload = function() {
                            if (logoutXhr.status === 200) {
                                localStorage.clear();
                                window.location.href = "/";
                            }
                        };
                        logoutXhr.onerror = function() {
                            console.error("Error occurred while logging out.");
                        };
                        logoutXhr.send();
                    } else {
                        alert('Hubo un error al eliminar la cuenta.');
                    }
                };
                xhr.send('id_usuario=' + encodeURIComponent(userId) + '&eliminar=true');
            }
        });
    </script>
</body>
</html>