<?php
$title = 'Detalles del NFT - Dialpi NFT';
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php include __DIR__ . '/../includes/head.php'; ?>
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
                            echo '<div class="price-cart">';
                            echo '<p>Precio: ' . htmlspecialchars($row["precio"]) . ' ETH</p>';
                            if ($row["disponible"] == 'Sí') {
                                echo '<form class="add-to-cart-form" method="POST" action="/nft?id=' . $id_nft . '">';
                                echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($row['id_nft']) . '">';
                                echo '<button type="submit" class="add-to-cart"><i class="fas fa-shopping-cart"></i> Agregar al carrito</button>';
                                echo '</form>';
                            } else {
                                echo '<button style="cursor: not-allowed;" type="button" class="add-to-cart-disabled" disabled><i class="fas fa-shopping-cart"></i>No disponible</button>';
                            }
                            echo '</div>';
                            echo '<p><span style="color: ' . ($row["disponible"] == 'Sí' ? 'green' : 'red') . ';">' . ($row["disponible"] == 'Sí' ? 'DISPONIBLE' : 'NO DISPONIBLE') . '</span></p>';
                            echo '<p class="description-title">Descripción:</p>';
                            echo '<p class="nft-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>';
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
            <h3>Otros NFTs de la colección:</h3>
            <div class="collection-preview">
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
                                echo '<strong>' . ucfirst(htmlspecialchars($row["nombre_nft"])) . '</strong>';
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
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="script/script.js"></script>
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
                            $('html, body').animate({
                                scrollTop: 0
                            }, 'fast');
                            var messageHeader = $('#message-header');
                            messageHeader.text(response.message);
                            messageHeader.css('opacity', '1');
                            setTimeout(function() {
                                messageHeader.css('opacity', '0');
                            }, 3000);
                        }
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                });
            });
        });
    </script>
</body>
</html>
