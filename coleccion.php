<?php
    $title = 'Colección - Dialpi NFT';
    session_start();
    require_once 'conexion.php';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $product_id = $_POST['product_id'];
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        if (in_array($product_id, $_SESSION['cart'])) {
            $response['error'] = true;
            $response['message'] = "El NFT que intentas añadir ya está en el carrito.";
        } else {
            $_SESSION['cart'][] = $product_id;
            $response['error'] = false;
            $response['message'] = "El NFT ha sido añadido al carrito.";
        }

        echo json_encode($response);
        exit;
    }
?>
<?php include 'head.php'; ?>
    <div id="error-header" class="error-header"></div>
    <div id="message-header" class="message-header"></div>
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
            <?php
                if (isset($_GET['id'])) {
                    $id_coleccion = $_GET['id'];
                }
            
                $query = "SELECT * FROM Coleccion_NFT WHERE id_coleccion = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('i', $id_coleccion);
                $stmt->execute();
                $result = $stmt->get_result();
            
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo '<div class="collection-cover">';
                    echo '<img src="img/colecciones/' . $row["nombre_coleccion"] . '/' . $row["nombre_coleccion"] . '1.png" alt="' . $row["nombre_coleccion"] . '">';
                    echo '<div class="collection-info">';
                    echo '<h2>' . ucfirst($row["nombre_coleccion"]) . '</h2>';
                    echo '<p>Creador: ' . $row["creador"] . '</p>';
                    echo '</div>';
                    echo '</div>';
                    
                    $query_nft = "SELECT * FROM NFT WHERE coleccion = ?";
                    $stmt_nft = $conexion->prepare($query_nft);
                    $stmt_nft->bind_param('i', $id_coleccion);
                    $stmt_nft->execute();
                    $result_nft = $stmt_nft->get_result();
                    
                    if ($result_nft->num_rows > 0) {
                        echo '<div class="nft-grid">';
                        while ($row_nft = $result_nft->fetch_assoc()) {
                            echo '<div class="nft-box">';
                            echo '<a href="/nft?id=' . $row_nft["id_nft"] . '">';
                            echo '<img src="img/colecciones/' . $row["nombre_coleccion"] . '/' . $row_nft["nombre_nft"] . '.png" alt="' . $row_nft["nombre_nft"] . '">';
                            echo '<h3>' . ucfirst($row_nft["nombre_nft"]) . '</h3>';
                            echo '<p>Precio: ' . $row_nft["precio"] . ' ETH</p>';
                            echo '</a>';
                            echo '<div class="button-cart-container">';
                            echo '<form class="add-to-cart-form" method="POST" action="">';
                            echo '<input type="hidden" name="product_id" value=' . $row_nft['id_nft'] . '>';
                            echo '<button type="submit" class="add-to-cart"><i class="fas fa-shopping-cart"></i>Agregar al carrito</button>';
                            echo '</form>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No se han encontrado NFT en esta colección.</p>';
                    }
                } else {
                    echo '<p>No se ha encontrado la colección.</p>';
                }
            ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
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
                            location.reload();
                        }
                    }
                });
            });
        });
    </script>
    <script src="script/script.js"></script>
</body>
</html>