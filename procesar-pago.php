<?php
$title = 'Procesar pago';
session_start();
require_once 'conexion.php';
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
    $mes = $_POST["Mes"];
    $year = $_POST["Year"];

    $meses = [
        "enero" => 1,
        "febrero" => 2,
        "marzo" => 3,
        "abril" => 4,
        "mayo" => 5,
        "junio" => 6,
        "julio" => 7,
        "agosto" => 8,
        "septiembre" => 9,
        "octubre" => 10,
        "noviembre" => 11,
        "diciembre" => 12
    ];

    $fechaActual = new DateTime();
    $fechaVencimiento = new DateTime($year . '-' . $meses[$mes] . '-01');

    if ($fechaVencimiento < $fechaActual) {
        echo "La tarjeta ha caducado";
    } else {
        $_SESSION['pago_realizado'] = true;

        foreach ($_SESSION['cart'] as $productId) {
            $sql = "UPDATE NFT SET disponible = 'No' WHERE id_nft = ?";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, "i", $productId);
            $result = mysqli_stmt_execute($stmt);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnLogin'])) {
    $_SESSION['pago_realizado'] = true;
}

if (isset($_GET['procesado'])) {
    if (isset($_SESSION['invitado'])) {
        unset($_SESSION['invitado']);
    }

    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }

    header('Location: /');
    exit;
}

include 'head.php';

$numeroFactura = null;

if (isset($_SESSION['pago_realizado'])) {
    do {
        $numeroFactura = generarNumeroFactura();
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

    echo "<div style='display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column;'>";
    echo "<h1>Procesando pago...</h1>";
    echo "<img src='img/procesando.gif' alt='Procesando...' height=100>";
    echo "</div>";

    echo "<script>
        setTimeout(function() {
            location.href = '/procesar-pago?confirmar';
        }, 5000);
    </script>";
    unset($_SESSION['pago_realizado']);
}

if (isset($_GET['confirmar'])) {
    include 'header-center.php';
    ?>
    <main>
        <div class="error-404">
            <h1>El pago se ha realizado correctamente.</h1>
            <h2>Número de pedido <?php echo $numeroFactura; ?></h2>
            <a href="/certificado" target="_blank">Pulsa aquí para descargar tu certificado.</a><br>
            <img src="/img/exito.png" alt="Éxito">
            <p>Recuerda que siempre puedes descargarlo desde tu perfil. <a href="/procesar-pago.php?procesado=1" id="volver-a-inicio">Volver a inicio.</a></p>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="script/script.js"></script>
</body>
</html>
<?php
}
?>