<?php
$title = 'Mis NFT';
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$usuario_id = $_SESSION['usuario']['id'];

$query = "SELECT NFT.*, Coleccion_NFT.nombre_coleccion FROM NFT 
    INNER JOIN Detalle_Pedido ON NFT.id_nft = Detalle_Pedido.id_nft
    INNER JOIN Pedidos_NFT ON Detalle_Pedido.id_pedido = Pedidos_NFT.id_pedido
    INNER JOIN Coleccion_NFT ON NFT.coleccion = Coleccion_NFT.id_coleccion
    WHERE Pedidos_NFT.id_usuario = ?";
$stmt = mysqli_prepare($conexion, $query);

mysqli_stmt_bind_param($stmt, 'i', $usuario_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$nfts = mysqli_fetch_all($result, MYSQLI_ASSOC);

include __DIR__ . '/../includes/head.php';
    include __DIR__ . '/../includes/header.php';
    ?>
    <main>
        <div class="container">
            <h2>Mis NFT</h2>
            <?php if (empty($nfts)): ?>
                <p>Actualmente no tienes NFTs comprados.</p>
            <?php else: ?>
                <div class="mis-nft-grid">
                    <?php foreach ($nfts as $nft): ?>
                        <div class="nft">
                            <?php echo '<img src="img/colecciones/' . $nft["nombre_coleccion"] . '/' . $nft["nombre_nft"] . '.png" alt="' . $nft["nombre_nft"] . '">'; ?>
                            <p class="nft-name"><?php echo ucfirst($nft["nombre_nft"]); ?></p>
                            <p><span class="nft-label">Precio de compra:</span> <span class="nft-value"><?php echo $nft["precio"]; ?> ETH</span></p>
                            <a href="/certificado?id=<?php echo $nft['id_nft']; ?>" target="_blank">Descargar Certificado</a>
                            <a href="/vender-nft?id=<?php echo $nft['id_nft']; ?>" class="boton-vender">Vender NFT</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="script/script.js"></script>
</body>
</html>