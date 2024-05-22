<?php
$title = 'Procesar pago';
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function generarNumeroFactura() {
    return mt_rand(10000000, 99999999);
}

function facturaExiste($numeroFactura, $conexion) {
    $stmt = $conexion->prepare("SELECT 1 FROM Pedidos_NFT WHERE id_pedido = ?");
    $stmt->bind_param("i", $numeroFactura);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['boton-proceder'])) {
    $mes = $_POST["mes"];
    $year = '20' . $_POST["year"];

    $fechaActual = new DateTime();
    $fechaVencimiento = new DateTime($year . '-' . $mes . '-01');

    if ($fechaVencimiento < $fechaActual) {
        echo "La tarjeta ha caducado";
    } else {
        $_SESSION['pago_realizado'] = true;
        unset($_SESSION['checkout']);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['boton-proceder-2'])) {
    $_SESSION['pago_realizado'] = true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar-tarjeta'])) {
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
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnLogin'])) {
    $_SESSION['pago_realizado'] = true;
}

if (isset($_GET['procesado'])) {
    unset($_SESSION['cart']);
    unset($_SESSION['numero_factura']);
    unset($_SESSION['pago_realizado']);
    unset($_SESSION['pago_confirmado']);

    if (isset($_SESSION['invitado'])) {
        unset($_SESSION['invitado']);
    }

    header('Location: /');
    exit;
}

include __DIR__ . '/../includes/head.php';

if (isset($_SESSION['pago_realizado']) && !isset($_SESSION['pago_confirmado'])) {
    $_SESSION['pago_confirmado'] = true;

    do {
        $numeroFactura = generarNumeroFactura();
        $_SESSION['numero_factura'] = $numeroFactura;
    } while (facturaExiste($numeroFactura, $conexion));
    
    if (isset($_SESSION['usuario'])) {
        $stmt = $conexion->prepare("INSERT INTO Pedidos_NFT (id_pedido, id_usuario) VALUES (?, ?)");
        $stmt->bind_param("ii", $numeroFactura, $_SESSION['usuario']['id']);
        $stmt->execute();

        foreach ($_SESSION['cart'] as $productId) {
            $stmt = $conexion->prepare("INSERT INTO Detalle_Pedido (id_pedido, id_nft) VALUES (?, ?)");
            $stmt->bind_param("ii", $numeroFactura, $productId);
            $stmt->execute();
        }
    } else {
        $stmt = $conexion->prepare("INSERT INTO Pedidos_NFT_Invitados (id_pedido, nombre_invitado, apellidos_invitado, dni_invitado) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $numeroFactura, $_SESSION['invitado']['nombre'], $_SESSION['invitado']['apellidos'], $_SESSION['invitado']['dni']);
        $stmt->execute();

        foreach ($_SESSION['cart'] as $productId) {
            $stmt = $conexion->prepare("INSERT INTO Detalle_Pedido_Invitado (id_pedido, id_nft) VALUES (?, ?)");
            $stmt->bind_param("ii", $numeroFactura, $productId);
            $stmt->execute();
        }
    }

    foreach ($_SESSION['cart'] as $productId) {
        $sql = "UPDATE NFT SET disponible = 'No' WHERE id_nft = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        $result = mysqli_stmt_execute($stmt);
    }

    if (isset($_SESSION['usuario'])) {
        $usuario_id = $_SESSION['usuario']['id'];
        
        $nfts_en_cadena = '';
        $primero = true;

        foreach ($_SESSION['cart'] as $productId) {
            if (!$primero) {
                $nfts_en_cadena .= ';';
            } else {
                $primero = false;
            }
            $nfts_en_cadena .= $productId;
        }
    
        $stmt = $conexion->prepare("SELECT NFT_comprado FROM Usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $nfts_comprados_actual = $usuario['NFT_comprado'];
    
        if ($nfts_comprados_actual !== NULL) {
            $nfts_en_cadena = $usuario['NFT_comprado'] . ';' . $nfts_en_cadena;
        }
    
        $stmt = $conexion->prepare("UPDATE Usuarios SET NFT_comprado = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $nfts_en_cadena, $usuario_id);
        $stmt->execute();
    }

    ?>
    <div class="procesar-pago">
        <h1>Procesando pago...</h1>
        <img src="/img/procesando.gif" alt="Procesando..." height=100>
    </div>
    <script>
        setTimeout(function() {
            location.href = '/procesar-pago?confirmar';
        }, 5000);
    </script>
    <?php
} else {
    header('Location: /carrito');
    exit;
}

if (isset($_GET['confirmar'])) {
    include __DIR__ . '/../includes/header-center.php';
    ?>
    <main>
        <div class="error-404">
            <h1>El pago se ha realizado correctamente.</h1>
            <h2>Número de pedido: <?php echo $_SESSION['numero_factura']; ?></h2>
            <a href="/certificado" target="_blank">Pulsa aquí para descargar tu certificado.</a><br>
            <img src="/img/exito.png" alt="Éxito">
            <p>Recuerda que siempre puedes descargarlo desde tu perfil. <a href="/procesar-pago.php?procesado=1" id="volver-a-inicio">Volver a inicio.</a></p>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php';
}
?>
    <script src="script/script.js"></script>
</body>
</html>