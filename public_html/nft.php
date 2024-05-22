<?php
$title = 'Detalles del NFT - Dialpi NFT';
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (in_array($product_id, $_SESSION['cart'])) {
        $response = [
            'error' => true,
            'message' => "El NFT que intentas añadir ya está en el carrito."
        ];
    } else {
        $_SESSION['cart'][] = $product_id;
        $response = [
            'error' => false,
            'message' => "El NFT ha sido añadido al carrito."
        ];
    }

    echo json_encode($response);
    exit;
}

include __DIR__ . '/../includes/head.php';
?>
<div id="error-header" class="error-header"></div>
<div id="message-header" class="message-header"></div>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main>
    <div class="container">
        <div class="nft-details-container">
            <?php
            if (isset($_GET['id'])) {
                $id_nft = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

                $query = "SELECT * FROM NFT WHERE id_nft = ?";
                if ($stmt = $conexion->prepare($query)) {
                    $stmt->bind_param('i', $id_nft);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $coleccion_query = "SELECT nombre_coleccion FROM Coleccion_NFT WHERE id_coleccion = ?";
                        if ($coleccion_stmt = $conexion->prepare($coleccion_query)) {
                            $coleccion_stmt->bind_param('i', $row['coleccion']);
                            $coleccion_stmt->execute();
                            $coleccion_result = $coleccion_stmt->get_result();
                            if ($coleccion_result->num_rows > 0) {
                                $coleccion_row = $coleccion_result->fetch_assoc();
                                $nombre_coleccion = htmlspecialchars($coleccion_row['nombre_coleccion']);
                            } else {
                                $nombre_coleccion = 'desconocido';
                            }
                        } else {
                            $nombre_coleccion = 'desconocido';
                        }

                        echo '<div class="nft-details">';
                        echo '<div class="nft-image">';
                        $imagePath = 'img/colecciones/' . $nombre_coleccion . '/' . htmlspecialchars($row["nombre_nft"]) . '.png';
                        echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($row["nombre_nft"]) . '">';
                        echo '</div>';
                        echo '<div class="nft-info">';
                        echo '<h2>' . ucfirst(htmlspecialchars($row["nombre_nft"])) . '</h2>';
                        echo '<p>Precio: ' . htmlspecialchars($row["precio"]) . ' ETH</p>';
                        echo '<p><span style="color: ' . ($row["disponible"] ? 'green' : 'red') . ';">' . ($row["disponible"] ? 'DISPONIBLE' : 'NO DISPONIBLE') . '</span></p>';
                        echo '<div class="button-cart-container-2">';
                        echo '<form class="add-to-cart-form" method="POST" action="/nft?id=' . $id_nft . '">';
                        echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($row['id_nft']) . '">';
                        echo '<button type="submit" class="add-to-cart"><i class="fas fa-shopping-cart"></i> Agregar al carrito</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<p>No se ha encontrado el NFT.</p>';
                    }
                } else {
                    echo '<p>Error en la consulta.</p>';
                }
            } else {
                echo '<p>No se ha proporcionado un ID de NFT.</p>';
            }
            ?>
        </div>
        <div class="collection-preview">
            <h3>Otros NFTs de la colección:</h3>
            <?php
            if (isset($nombre_coleccion)) {
                $query = "SELECT * FROM NFT WHERE coleccion = ? AND id_nft != ?";
                if ($stmt = $conexion->prepare($query)) {
                    $stmt->bind_param('ii', $row['coleccion'], $id_nft);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="collection-item">';
                            echo '<a href="nft.php?id=' . htmlspecialchars($row["id_nft"]) . '">';
                            echo '<img src="img/colecciones/' . $nombre_coleccion . '/' . htmlspecialchars($row["nombre_nft"]) . '.png" alt="' . htmlspecialchars($row["nombre_nft"]) . '">';
                            echo '</a>';
                            echo '<p>' . ucfirst(htmlspecialchars($row["nombre_nft"])) . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No hay otros NFT en esta colección.</p>';
                    }
                } else {
                    echo '<p>Error al obtener la colección.</p>';
                }
            }
            ?>
        </div>
    </div>
</main>
<style>
    .nft-details-container {
        display: flex;
    }

    .nft-details {
        display: flex;
        flex-wrap: wrap;
    }

    .nft-image {
        flex: 0 0 40%; /* Ajustar según la proporción deseada */
    }

    .nft-image img {
        width: 100%;
        height: auto;
    }

    .nft-info {
        flex: 0 0 60%; /* Ajustar según la proporción deseada */
        padding-left: 20px;
    }

    .nft-info h2 {
        margin-top: 0;
        font-size: 24px; /* Tamaño de fuente del título */
        margin-bottom: 10px;
    }

    .nft-info p {
        font-size: 18px; /* Tamaño de fuente del texto */
        margin-bottom: 8px;
    }

    .button-cart-container-2 {
        margin-top: 20px;
    }

    .collection-preview {
        flex: 0 0 30%;
        padding-left: 20px;
        border-left: 1px solid #ccc;
    }

    .collection-preview h3 {
        margin-top: 0;
    }

    .collection-item {
        margin-bottom: 20px;
        text-align: center;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .collection-item img {
        width: calc(25% - 10px); /* Ajustar según el espacio disponible */
        margin-right: 20px; /* Espacio entre las imágenes */
        margin-bottom: 20px;
    }

    .collection-item:nth-child(4n+4) img {
        margin-right: 0; /* Eliminar el margen derecho en el cuarto elemento de cada fila */
    }

    .collection-item:last-child img {
        margin-right: 0; /* Eliminar el margen derecho en el último elemento de la lista */
    }

    .collection-item p {
        display: none;
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
    $(document).ready(function() {
        $('.add-to-cart-form').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '/coleccion',
                method: 'POST',
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        var errorHeader = $('#error-header');
                        errorHeader.text(response.message);
                        errorHeader.css('opacity', '1');
                        setTimeout(function() {
                            errorHeader.css('opacity', '0');
                        }, 3000);
                    } else {
                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                        var messageHeader = $('#message-header');
                        messageHeader.text(response.message);
                        messageHeader.css('opacity', '1');
                        setTimeout(function() {
                            messageHeader.css('opacity', '0');
                        }, 3000);
                    }
                }
            });
        });
    });
</script>
<script src="script/script.js"></script>
</body>
</html>