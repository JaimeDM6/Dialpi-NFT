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
    $query = "SELECT * FROM NFT WHERE id_nft = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('i', $nft_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $nft = $result->fetch_assoc();
        $precio_venta = $nft['precio'] * 0.8;
    } else {
        echo 'No se encontró el NFT con el ID proporcionado.';
    }
} else {
    header('Location: /login');
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="container">
        <h2>Vender NFT</h2>
        <?php if (isset($nft)): ?>
            <p>Para vender un NFT, tienes que subir el certificado original del NFT en formato PDF. Si no lo tienes descargado, puedes </p>
            <form action="procesar_venta.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="nft_id" value="<?php echo $nft_id; ?>">
                <div>
                    <label for="certificado">Subir certificado:</label>
                    <input type="file" id="certificado" name="certificado" required>
                </div>
                <div>
                    <label for="precio_venta">Precio de venta:</label>
                    <input type="text" id="precio_venta" name="precio_venta" value="<?php echo $precio_venta; ?>" required>
                </div>
                <button type="submit">Vender NFT</button>
            </form>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="script/script.js"></script>
</body>
</html>