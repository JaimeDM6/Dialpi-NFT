<?php
$title = 'Procesar venta';
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../includes/head.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['certificado'])) {
        $nombreArchivo = $_FILES['certificado']['name'];
        $partesNombre = explode('-', $nombreArchivo);
        if (count($partesNombre) == 3) {
            $tokenArchivo = $partesNombre[2];
            $tokenArchivo = str_replace('.pdf', '', $tokenArchivo);

            $nft_id = $_POST['nft_id'];
            $query = "SELECT token_nft FROM NFT WHERE id_nft = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param('i', $nft_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $nft = $result->fetch_assoc();
                $tokenBBDD = $nft['token_nft'];

                if ($tokenArchivo == $tokenBBDD) {
                    $query = "UPDATE NFT SET disponible = 'Sí', propietario_id = NULL WHERE id_nft = ?";
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param('i', $nft_id);
                    $stmt->execute();

                    $query = "SELECT * FROM Detalle_Pedido WHERE id_nft = ?";
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param('i', $nft_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $detalle_pedido = $result->fetch_assoc();
                    $id_pedido = $detalle_pedido['id_pedido'];

                    $query = "DELETE FROM Detalle_Pedido WHERE id_pedido = ? AND id_nft = ?";
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param('ii', $id_pedido, $nft_id);
                    $stmt->execute();

                    $query = "SELECT * FROM Detalle_Pedido WHERE id_pedido = ?";
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param('i', $id_pedido);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows == 0) {
                        $query = "DELETE FROM Pedidos_NFT WHERE id_pedido = ?";
                        $stmt = $conexion->prepare($query);
                        $stmt->bind_param('i', $id_pedido);
                        $stmt->execute();
                    }
                
                    $usuario_id = $_SESSION['usuario']['id'];
                    $query = "SELECT NFT_comprado, NFT_vendido FROM Usuarios WHERE id_usuario = ?";
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param('i', $usuario_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $usuario = $result->fetch_assoc();
                    $nft_comprados = $usuario['NFT_comprado'];
                    $nft_vendidos = $usuario['NFT_vendido'] ? $usuario['NFT_vendido'] : '';

                    $nft_comprados = explode(';', $nft_comprados);
                    $nft_comprados = array_diff($nft_comprados, array($nft_id));
                    $nft_comprados = implode(';', $nft_comprados);

                    if ($nft_comprados === '') {
                        $nft_comprados = NULL;
                    }

                    $nft_vendidos = explode(';', $nft_vendidos);
                    if (!in_array($nft_id, $nft_vendidos)) {
                        $nft_vendidos[] = $nft_id;
                    }
                    $nft_vendidos = implode(';', $nft_vendidos);

                    $query = "UPDATE Usuarios SET NFT_comprado = ?, NFT_vendido = ? WHERE id_usuario = ?";
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param('ssi', $nft_comprados, $nft_vendidos, $usuario_id);
                    $stmt->execute();                    
                    ?>
                    <div class="procesar-pago">
                        <h1>Procesando venta...</h1>
                        <img src="/img/procesando.gif" alt="Procesando..." height=100>
                    </div>
                    <script>
                        setTimeout(function() {
                            location.href = '/procesar-venta?confirmar';
                        }, 5000);
                    </script>
                    <?php
                } else {
                    echo "<script type='text/javascript'>
                            alert('El token del archivo no coincide con el token almacenado en la base de datos.');
                            window.location.href = '/vender-nft';
                        </script>";
                }
            } else {
                echo "<script type='text/javascript'>
                        alert('No se encontró el NFT con el ID proporcionado.');
                        window.location.href = '/vender-nft';
                    </script>";
            }
        } else {
            echo "<script type='text/javascript'>
                    alert('El nombre del archivo no tiene el formato correcto.');
                    window.location.href = '/vender-nft';
                </script>";
        }
    }
}

if (isset($_GET['confirmar'])) {
    include __DIR__ . '/../includes/header-center.php';
    ?>
    <main>
        <div class="error-404">
            <h1>La venta se ha realizado correctamente.</h1>
            <img src="/img/exito.png" alt="Éxito"><br>
            <p>Recibirás la transferencia en un plazo de 2 a 3 días. <a href="/">Volver a inicio.</a></p>
        </div>
    </main>
    <?php
    include __DIR__ . '/../includes/footer.php';
?>
    <script src="script/script.js"></script>
</body>
</html>
<?php
}
?>