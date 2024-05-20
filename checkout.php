<?php
session_start();
ob_start();
require_once 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['cart'])) {
    header('Location: /carrito');
    exit;
}

$title = "Checkout";

if (isset($_GET)) {
    $parametro = array_key_first($_GET);
    
    switch ($parametro) {
        case 'invitado':
            $title = 'Checkout - Invitado';
            break;
        case 'direccion':
            $title = 'Checkout - Dirección';
            break;
        case 'direccion-edit':
            $title = 'Checkout - Dirección';
            break;
        case 'metodo-pago':
            $title = 'Checkout - Método de Pago';
            break;
        default:
            $title = 'Checkout';
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['invitado'])) {
    $dni = $_POST['dni'];

    function validar_dni($dni) {
        $letra = substr($dni, -1);
        $numeros = substr($dni, 0, -1);
        return strtoupper($letra) === substr('TRWAGMYFPDXBNJZSQVHLCKE', strtr($numeros, 'XYZ', '012')%23, 1);
    }

    if (!validar_dni($dni)) {
        $response['error'] = true;
        $response['message'] = 'El DNI no es válido.';
    } else {
        $_SESSION['invitado'] = [
            'nombre' => $_POST['nombre'],
            'apellidos' => $_POST['apellidos'],
            'dni' => $_POST['dni'],
            'email' => $_POST['email'],
            'telefono' => $_POST['telefono'],
        ];

        $response['message'] = 'success';
    }

    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarDireccion'])) {
    $idUsuario = $_SESSION['usuario']['id'];
    $query = "UPDATE Usuarios SET direccion_usuario = NULL, cp_usuario = NULL, poblacion_usuario = NULL, estado_provincia = NULL, pais_usuario = NULL WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    echo json_encode(['success' => $stmt->affected_rows > 0]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarDireccionInvitado'])) {
    $_SESSION['invitado'] = [
        'nombre' => $_SESSION['invitado']['nombre'],
        'apellidos' => $_SESSION['invitado']['apellidos'],
        'dni' => $_SESSION['invitado']['dni'],
        'email' => $_SESSION['invitado']['email'],
        'telefono' => $_SESSION['invitado']['telefono'],
        'direccion' => '',
        'cp' => '',
        'poblacion' => '',
        'estado' => '',
        'pais' => '',
    ];
    echo json_encode(['success' => true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/intlTelInput.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Amaranth" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/grover-heavy" rel="stylesheet">
    <script src="https://kit.fontawesome.com/ea577ecbca.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
            <h2>Checkout</h2>
            <?php
                $parametro = array_key_first($_GET);
                $paso = 0;

                if (!isset($_SESSION['cart'])) {
                    echo "No hay pedidos en el carrito.";
                }
                switch ($parametro) {
                    case 'invitado':
                        if (isset($_SESSION['cart'])) {
                        ?>
                            <div class="invitado">
                                <form method="POST" action="/checkout?direccion" class="invitado-form">
                                    <p id="error-message" style="color: red;"></p>
                                    <label for="nombre">Nombre:</label><br>
                                    <input type="text" id="nombre" name="nombre" required autocomplete="name"><br>
                                    <label for="apellidos">Apellidos:</label><br>
                                    <input type="text" id="apellidos" name="apellidos" required autocomplete="family-name"><br>
                                    <label for="dni">DNI:</label><br>
                                    <input type="text" id="dni" name="dni" required maxlength="9" autocomplete="off"><br>
                                    <label for="email">Correo electrónico:</label><br>
                                    <input type="email" id="email" name="email" required autocomplete="email"><br>
                                    <label for="telefono">Número de teléfono:</label><br>
                                    <input type="tel" id="telefono" name="telefono" required placeholder="Introduce tu número de teléfono">
                                    <input type="submit" value="Siguiente">
                                </form>
                            </div>
                        <?php
                        }
                        break;
                    case 'direccion':
                        if (isset($_SESSION['cart'])) {
                            if (isset($_SESSION['usuario'])) {
                                $idUsuario = $_SESSION['usuario']['id'];
                            
                                $query = "SELECT direccion_usuario, cp_usuario, poblacion_usuario, estado_provincia, pais_usuario FROM Usuarios WHERE id_usuario = ?";
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
                                            <button onclick="location.href='/checkout?direccion-edit'">Editar</button>
                                            <button id="eliminar-direccion">Eliminar</button>
                                        </div>
                                    </div>
                                    <div class="boton-derecha">
                                        <button class="siguiente" onclick="location.href='/checkout?metodo-pago'">Seleccionar</button>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <div class="direccion">
                                        <p>No hay ninguna dirección guardada.</p><br>
                                        <div class="botones-centrados">
                                            <button onclick="location.href='/checkout?direccion-edit'">Añadir</button>
                                        </div>
                                    </div>
                                <?php
                                }
                            } else {
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $_SESSION['invitado'] = [
                                        'nombre' => $_SESSION['invitado']['nombre'],
                                        'apellidos' => $_SESSION['invitado']['apellidos'],
                                        'dni' => $_SESSION['invitado']['dni'],
                                        'email' => $_SESSION['invitado']['email'],
                                        'telefono' => $_SESSION['invitado']['telefono'],
                                        'direccion' => isset($_POST['direccion']) ? $_POST['direccion'] : '',
                                        'cp' => isset($_POST['cp']) ? $_POST['cp'] : '',
                                        'poblacion' => isset($_POST['poblacion']) ? $_POST['poblacion'] : '',
                                        'estado' => isset($_POST['estado']) ? $_POST['estado'] : '',
                                        'pais' => isset($_POST['pais']) ? $_POST['pais'] : '',
                                    ];
                                }

                                if (isset($_SESSION['invitado']['direccion']) && $_SESSION['invitado']['direccion'] !== '') {
                                ?>
                                    <div class="direccion">
                                        <h3>Dirección de facturación</h3>
                                        <p>Dirección: <?= $_SESSION['invitado']['direccion'] ?></p>
                                        <p>Código Postal: <?= $_SESSION['invitado']['cp'] ?></p>
                                        <p>Población: <?= $_SESSION['invitado']['poblacion'] ?></p>
                                        <p>Estado/Provincia: <?= $_SESSION['invitado']['estado'] ?></p>
                                        <p>País: <?= $_SESSION['invitado']['pais'] ?></p><br>
                                        <div class="botones-centrados">
                                            <button onclick="location.href='/checkout?direccion-edit'">Editar</button>
                                            <button id="eliminar-direccion-invitado">Eliminar</button>
                                        </div>
                                    </div>
                                    <div class="boton-derecha">
                                        <button class="siguiente" onclick="location.href='/checkout?metodo-pago'">Seleccionar</button>
                                    </div>
                                <?php
                                } else {
                                    ?>
                                    <div class="direccion">
                                        <p>No hay ninguna dirección guardada.</p><br>
                                        <div class="botones-centrados">
                                            <button onclick="location.href='/checkout?direccion-edit'">Añadir</button>
                                        </div>
                                    </div>
                                <?php
                                }
                            }
                        }
                        break;
                    case 'direccion-edit':
                        if (isset($_SESSION['cart'])) {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $direccion = $_POST['direccion'];
                                $cp = $_POST['cp'];
                                $poblacion = $_POST['poblacion'];
                                $estado = $_POST['estado'];
                                $pais = $_POST['pais'];

                                $query = "UPDATE Usuarios SET direccion_usuario = ?, cp_usuario = ?, poblacion_usuario = ?, estado_provincia = ?, pais_usuario = ? WHERE id_usuario = ?";

                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('sssssi', $direccion, $cp, $poblacion, $estado, $pais, $_SESSION['usuario']['id']);
                                $stmt->execute();

                                if ($stmt->affected_rows > 0) {
                                    header('Location: /checkout?direccion');
                                } else {
                                }

                                $stmt->close();
                            }

                            if (isset($_SESSION['usuario'])) {
                                $idUsuario = $_SESSION['usuario']['id'];

                                $query = "SELECT direccion_usuario, cp_usuario, poblacion_usuario, estado_provincia, pais_usuario FROM Usuarios WHERE id_usuario = ?";
                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('i', $idUsuario);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $direccion = $result->fetch_assoc();
                            ?>
                                <form action="checkout?direccion-edit" method="post" class="direccion-form">
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
                                        <a href="/checkout?direccion" class="cancel-button">Cancelar</a>
                                    </div>
                                </form>
                            <?php
                            } else {
                            ?>
                                <form action="checkout?direccion" method="post" class="direccion-form">
                                    <h3>Dirección de facturación</h3>
                                    <label for="direccion">Dirección:</label>
                                    <input type="text" id="direccion" name="direccion" value="<?= isset($_SESSION['invitado']['direccion']) ? $_SESSION['invitado']['direccion'] : '' ?>">
                                    <label for="cp">Código Postal:</label>
                                    <input type="text" id="cp" name="cp" value="<?= isset($_SESSION['invitado']['cp']) ? $_SESSION['invitado']['cp'] : '' ?>">
                                    <label for="poblacion">Población:</label>
                                    <input type="text" id="poblacion" name="poblacion" value="<?= isset($_SESSION['invitado']['poblacion']) ? $_SESSION['invitado']['poblacion'] : '' ?>">
                                    <label for="estado">Estado/Provincia:</label>
                                    <input type="text" id="estado" name="estado" value="<?= isset($_SESSION['invitado']['estado']) ? $_SESSION['invitado']['estado'] : '' ?>">
                                    <label for="pais">País:</label>
                                    <?php include 'select_paises.php'; ?>
                                    <br>
                                    <div class="direccion-button">
                                        <input type="submit" value="Guardar">
                                        <a href="/checkout?direccion" class="cancel-button">Cancelar</a>
                                    </div>
                                </form>
                            <?php
                            }
                        }
                        break;
                    case 'metodo-pago':
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
                    
                        if (isset($_SESSION['cart'])) {
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
                                            
                                            echo "<div class='metodo-pago-botones'>";
                                            if ($tarjetaCaducada) {
                                                echo "<button type='submit' style='background-color: grey; color: white; cursor: not-allowed;' class='boton-proceder tarjeta-caducada' name='boton-proceder-2' disabled>Pagar con tarjeta</button>";
                                            } else {
                                                echo "<button type='submit' formaction='/procesar-pago' class='boton-proceder' name='boton-proceder-2'>Pagar con tarjeta</button>";
                                            }
                                            echo "<button class='boton-paypal' name='boton-paypal'><a href='/paypal'>Pagar con</a></button>";
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
                                                                    $meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
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
                                                <div class="guardar-tarjeta">
                                                    <input type="checkbox" id="guardar-tarjeta" name="guardar-tarjeta">
                                                    <label for="guardar-tarjeta">Guardar tarjeta para el próximo pago</label>
                                                </div>
                                                <div class="metodo-pago-botones">
                                                    <button type="submit" class="boton-proceder" name="boton-proceder">Pagar con tarjeta</button>
                                                    <button class="boton-paypal" name="boton-paypal"><a href="/paypal">Pagar con</a></button>
                                                </div>
                                            </form>
                                        <?php
                                        }
                                    }
                                } else {
                                    ?>
                                    <form class="tarjeta-credito" action="/procesar-pago" method="post">
                                        <h3 class="title">Método de pago</h3>
                                        <input type="text" name="titular-tarjeta" class="titular-tarjeta" placeholder="Titular de la tarjeta" autocomplete="cc-name" required>
                                        <input type="text" name="numero-tarjeta" class="numero-tarjeta" placeholder="Número de tarjeta" autocomplete="cc-number" maxlength="19" required>
                                        <div class="fecha-cvv">
                                            <div class="mes-tarjeta">
                                                <select name="mes" autocomplete="cc-exp-month" required>
                                                    <option value="enero">Enero</option>
                                                    <option value="febrero">Febrero</option>
                                                    <option value="marzo">Marzo</option>
                                                    <option value="abril">Abril</option>
                                                    <option value="mayo">Mayo</option>
                                                    <option value="junio">Junio</option>
                                                    <option value="julio">Julio</option>
                                                    <option value="agosto">Agosto</option>
                                                    <option value="septiembre">Septiembre</option>
                                                    <option value="octubre">Octubre</option>
                                                    <option value="noviembre">Noviembre</option>
                                                    <option value="diciembre">Diciembre</option>
                                                </select>
                                            </div>
                                            <div class="year-tarjeta">
                                                <?php
                                                $year = date("Y");
                                                echo '<select name="year" autocomplete="cc-exp-year" required>';
                                                for ($i = $year; $i <= $year + 15; $i++) {
                                                    echo "<option value='$i'>$i</option>";
                                                }
                                                echo '</select>';
                                                ?>
                                            </div>
                                            <div class="cvv">
                                                <input type="text" name="ccv" class="cvv-input" placeholder="CVV" autocomplete="cc-csc" maxlength="3" required>
                                            </div>
                                        </div>
                                        <div class="metodo-pago-botones">
                                            <button type="submit" class="boton-proceder" name="boton-proceder">Pagar con tarjeta</button>
                                            <button class="boton-paypal" name="boton-paypal"><a href="/paypal">Pagar con</a></button>
                                        </div>
                                    </form>
                                <?php
                                }
                                ?>
                        </div>
                        <?php
                        }
                        break;
                    default:
                        if ($paso === 0) {
                            if (isset($_SESSION['cart'])) {
                                if (isset($_SESSION['usuario']) || isset($_SESSION['invitado'])) {
                                    header('Location: /checkout?direccion');
                                    exit;
                                } else {
                                    ?>
                                    <div class="cuenta">
                                        <div class="cuenta-izq">
                                            <form method="POST" action="/login" class="login-form checkout-login">
                                                <p id="error-message" style="color: red;"></p>
                                                <label for="email">Correo electrónico:</label><br>
                                                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autocomplete="email"><br>
                                                <label for="password">Contraseña:</label><br>
                                                <div class="password-container">
                                                    <input type="password" id="password" name="password" required autocomplete="current-password">
                                                    <i class="fas fa-eye" id="toggle-password"></i>
                                                </div>
                                                <input type="submit" value="Iniciar sesión">
                                            </form>
                                        </div>
                                        <div class="cuenta-der">
                                            <a href="/checkout?invitado" class="login-button">Continuar como invitado</a>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                        }
                        $paso++;
                        break;
                }
            ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="script/script.js"></script>
    <script>
        $(document).ready(function(){
            $('.login-form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/login',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            $('#error-message').text(response.message);
                        } else if (response.message === 'success') {
                            window.location.href = '/checkout?direccion';
                        }
                    }
                });
            });

            $('#eliminar-direccion').on('click', function(e) {
                e.preventDefault();
            
                $.ajax({
                    url: '/checkout?direccion',
                    method: 'POST',
                    data: { eliminarDireccion: true },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('.direccion').html('<p>No hay ninguna dirección guardada.</p><br><div class="botones-centrados"><button onclick="location.href=\'/checkout?direccion-edit\'">Añadir</button></div>');
                        } else {
                            alert('Hubo un error al eliminar la dirección.');
                        }
                    }
                });
            });

            $('#eliminar-direccion-invitado').on('click', function(e) {
                e.preventDefault();
            
                $.ajax({
                    url: '/checkout?direccion',
                    method: 'POST',
                    data: { eliminarDireccionInvitado: true },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('.direccion').html('<p>No hay ninguna dirección guardada.</p><br><div class="botones-centrados"><button onclick="location.href=\'/checkout?direccion-edit\'">Añadir</button></div>');
                        } else {
                            alert('Hubo un error al eliminar la dirección.');
                        }
                    }
                });
            });

            $('.invitado-form').on('submit', function(e) {
                e.preventDefault();
        
                $.ajax({
                    url: '/checkout?invitado',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            $('#error-message').css('color', 'red');
                            $('#error-message').text(response.message);
                            $('html, body').animate({ scrollTop: 0 }, 'fast');
                        } else {
                            window.location.href = '/checkout?direccion';
                        }
                    }
                });
            });
        });

        document.querySelectorAll('.boton-eliminar').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                var confirmation = confirm('¿Estás seguro/a de que quieres eliminar esta tarjeta?');
                if (confirmation) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '/checkout?metodo-pago', true);
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
            var botonProceder = document.querySelector('.boton-proceder');
            if (botonProceder) {
                botonProceder.addEventListener('click', function(event) {
                    if (this.disabled) {
                        alert('No se puede proceder al pago, la tarjeta está caducada. Prueba a eliminar la tarjeta y añade una nueva.');
                        event.preventDefault();
                    }
                });
            }

            var numeroTarjeta = document.querySelector('.numero-tarjeta');
            var cvv = document.querySelector('.cvv-input');

            cvv.addEventListener('input', function() {
                var valor = this.value.replace(/\D/g, '');
                this.value = valor;
            });

            numeroTarjeta.addEventListener('input', updateCardInfo);

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
                    this.style.backgroundPosition = 'right 10px center';
                    this.style.backgroundSize = 'auto 20px';
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
    </script>
    <script src="script/intlTelInputWithUtils.js"></script>
    <script>
        const input = document.querySelector("#telefono");
        window.intlTelInput(input, {
            initialCountry: "es",
        });
    </script>
</body>
</html>