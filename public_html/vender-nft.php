<?php
$title = 'Vender NFT';
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $nft_id = $_GET['id'];
    $usuario_id = $_SESSION['usuario']['id'];
    $query = "SELECT Usuarios.id_usuario, Usuarios.nombre_usuario, Usuarios.NFT_comprado, NFT.id_nft, NFT.nombre_nft 
              FROM Usuarios 
              INNER JOIN NFT ON Usuarios.id_usuario = NFT.propietario_id 
              WHERE Usuarios.id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $nft_comprados = explode(';', $usuario['NFT_comprado']);
        if (in_array($nft_id, $nft_comprados)) {
            $query = "SELECT * FROM NFT WHERE id_nft = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param('i', $nft_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $nft = $result->fetch_assoc();
            $precio_venta = $nft['precio'] * 0.8;
        } else {
            echo "<script type='text/javascript'>
                    alert('El usuario no ha comprado el NFT con el ID proporcionado.');
                    window.location.href = '/mis-nft';
                  </script>";
        }
    } else {
        echo "<script type='text/javascript'>
                alert('No se encontró el usuario con el ID proporcionado.');
                window.location.href = '/mis-nft';
              </script>";
    }
} else {
    header('Location: /mis-nft');
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="container">
        <h2>Vender NFT</h2>
        <?php if (isset($nft)): ?>
            <form action="/procesar-venta" method="post" enctype="multipart/form-data" class="login-form">
                <input type="hidden" name="nft_id" value="<?php echo $nft_id; ?>">
                <div>
                    <label class="vender-label" for="certificado">Subir certificado:</label>
                    <input type="file" id="certificado" name="certificado" required>
                </div>
                <div>
                    <label class="vender-label" for="precio_venta">Precio de venta:</label>
                    <p class="vender-nft" id="precio_venta"><?php echo $precio_venta; ?> ETH</p>
                </div>
                <input type="submit" value="Vender NFT">
            </form>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="script/script.js"></script>
</body>
</html>