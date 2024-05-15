<?php
session_start();
require_once 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['invitado'])) {
    $_SESSION['invitado'] = [
        'nombre' => $_POST['nombre'],
        'apellidos' => $_POST['apellidos'],
        'email' => $_POST['email'],
        'telefono' => $_POST['telefono'],
    ];
    header('Location: /checkout?direccion');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarDireccion'])) {
    $idUsuario = $_SESSION['id_usuario'];

    $query = "UPDATE Usuarios SET direccion_usuario = NULL, cp_usuario = NULL, poblacion_usuario = NULL, pais_usuario = NULL WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();

    echo json_encode(['success' => $stmt->affected_rows > 0]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarDireccionInvitado'])) {
    $_SESSION['invitado'] = [
        'direccion' => '',
        'cp' => '',
        'poblacion' => '',
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
    <title>Dialpi NFT</title>
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

                if (!isset($_SESSION['cart'])) {
                    echo "No hay pedidos en el carrito.";
                }

                switch ($parametro) {
                    case 'invitado':
                        if (isset($_SESSION['cart'])) {
                        ?>
                            <div class="invitado">
                                <form method="POST" action="/checkout?direccion" class="invitado-form">
                                    <label for="nombre">Nombre:</label><br>
                                    <input type="text" id="nombre" name="nombre" required autocomplete="name"><br>
                                    <label for="apellidos">Apellidos:</label><br>
                                    <input type="text" id="apellidos" name="apellidos" required autocomplete="family-name"><br>
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
                            if (isset($_SESSION['nombre_usuario'])) {
                                $idUsuario = $_SESSION['id_usuario'];
                            
                                $query = "SELECT direccion_usuario, cp_usuario, poblacion_usuario, pais_usuario FROM Usuarios WHERE id_usuario = ?";
                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('i', $idUsuario);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $direccion = $result->fetch_assoc();

                                if ($direccion['direccion_usuario'] !== null) {
                                ?>
                                    <div class="direccion">
                                        <h3>Dirección postal</h3>
                                        <p>Dirección: <?= $direccion['direccion_usuario'] ?></p>
                                        <p>Código Postal: <?= $direccion['cp_usuario'] ?></p>
                                        <p>Población: <?= $direccion['poblacion_usuario'] ?></p>
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
                                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['invitado'])) {
                                    $_SESSION['invitado'] = [
                                        'direccion' => $_POST['direccion'],
                                        'cp' => $_POST['cp'],
                                        'poblacion' => $_POST['poblacion'],
                                        'pais' => $_POST['pais'],
                                    ];
                                }

                                if (isset($_SESSION['invitado']['direccion']) && $_SESSION['invitado']['direccion'] !== '') {
                                ?>
                                    <div class="direccion">
                                        <h3>Dirección postal</h3>
                                        <p>Dirección: <?= $_SESSION['invitado']['direccion'] ?></p>
                                        <p>Código Postal: <?= $_SESSION['invitado']['cp'] ?></p>
                                        <p>Población: <?= $_SESSION['invitado']['poblacion'] ?></p>
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
                                $pais = $_POST['pais'];
                            
                                $query = "UPDATE Usuarios SET direccion_usuario = ?, cp_usuario = ?, poblacion_usuario = ?, pais_usuario = ? WHERE id_usuario = ?";
                            
                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('ssssi', $direccion, $cp, $poblacion, $pais, $_SESSION['id_usuario']);
                                $stmt->execute();
                            
                                if ($stmt->affected_rows > 0) {
                                    echo "Dirección actualizada con éxito.";
                                    header('Location: /checkout?direccion');
                                } else {
                                    echo "No se pudo actualizar la dirección.";
                                }
                            }

                            if (isset($_SESSION['nombre_usuario'])) {
                                $idUsuario = $_SESSION['id_usuario'];
                        
                                $query = "SELECT direccion_usuario, cp_usuario, poblacion_usuario, pais_usuario FROM Usuarios WHERE id_usuario = ?";
                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('i', $idUsuario);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $direccion = $result->fetch_assoc();
                            ?>
                                <form action="checkout?direccion-edit" method="post" class="direccion-form">
                                    <h3>Dirección postal</h3>
                                    <label for="direccion">Dirección:</label>
                                    <input type="text" id="direccion" name="direccion" value="<?= $direccion['direccion_usuario'] ?>">
                                    <label for="cp">Código Postal:</label>
                                    <input type="text" id="cp" name="cp" value="<?= $direccion['cp_usuario'] ?>">
                                    <label for="poblacion">Población:</label>
                                    <input type="text" id="poblacion" name="poblacion" value="<?= $direccion['poblacion_usuario'] ?>">
                                    <label for="pais">País:</label>
                                    <?php include 'select_paises.php'; ?>
                                    <br>
                                    <div class="direccion-button">
                                        <input type="submit" value="Guardar">
                                        <a href="/checkout?direccion" class="cancel-button">Cancelar</a>
                                    </div>
                                </form>
                            <?php
                            } else if (isset($_SESSION['invitado']['direccion']) && $_SESSION['invitado']['direccion'] !== '') {
                            ?>
                                <form action="checkout?direccion" method="post" class="direccion-form">
                                    <h3>Dirección postal</h3>
                                    <label for="direccion">Dirección:</label>
                                    <input type="text" id="direccion" name="direccion" value="<?= $_SESSION['invitado']['direccion'] ?>">
                                    <label for="cp">Código Postal:</label>
                                    <input type="text" id="cp" name="cp" value="<?= $_SESSION['invitado']['cp'] ?>">
                                    <label for="poblacion">Población:</label>
                                    <input type="text" id="poblacion" name="poblacion" value="<?= $_SESSION['invitado']['poblacion'] ?>">
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
                                    <h3>Dirección postal</h3>
                                    <label for="direccion">Dirección:</label>
                                    <input type="text" id="direccion" name="direccion">
                                    <label for="cp">Código Postal:</label>
                                    <input type="text" id="cp" name="cp">
                                    <label for="poblacion">Población:</label>
                                    <input type="text" id="poblacion" name="poblacion">
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
                        if (isset($_SESSION['cart'])) {
                        ?>
                            <div class="metodo-pago">
                            </div>
                        <?php
                        }
                        break;
                    case 'pago':
                        if (isset($_SESSION['cart'])) {
                        ?>
                            <div class="pago">
                            </div>
                        <?php
                        }
                        break;
                    default:
                        if (isset($_SESSION['cart'])) {
                            if (isset($_SESSION['nombre_usuario'])) {
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
                        } else {
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