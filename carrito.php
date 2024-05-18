<?php
    $title = 'Carrito';
    session_start();
    require_once 'conexion.php';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $product_id = $_POST['product_id'];
        if (isset($_SESSION['cart'])) {
            $key = array_search($product_id, $_SESSION['cart']);
            if ($key !== false) {
                unset($_SESSION['cart'][$key]);
            }

            if (empty($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }
        }
    }
?>
<?php include 'head.php'; ?>
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
            <h2>Carrito</h2>
            <?php
                $subtotal = 0;
                if (isset($_SESSION['cart'])) {
                    echo '<table>';
                    echo '<tr><th>NFT</th><th class="price-header">Precio</th></tr>';
                    foreach ($_SESSION['cart'] as $productId) {
                        $query = "SELECT * FROM NFT WHERE id_nft = ?";
                        $stmt = $conexion->prepare($query);
                        $stmt->bind_param('i', $productId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();

                            $query2 = "SELECT nombre_coleccion FROM Coleccion_NFT WHERE id_coleccion = ?";
                            $stmt2 = $conexion->prepare($query2);
                            $stmt2->bind_param('i', $row["coleccion"]);
                            $stmt2->execute();
                            $result2 = $stmt2->get_result();
                            $row2 = $result2->fetch_assoc();

                            echo '<tr class="cart-item">';
                            echo '<td class="cart-item-title"><img src="img/colecciones/' . $row2["nombre_coleccion"] . '/' . $row["nombre_nft"] . '.png" alt="' . ucfirst($row["nombre_nft"]) . '">' . ucfirst($row["nombre_nft"]) . '</td>';
                            echo '<td class="cart-item-price">' . $row["precio"] . ' ETH <button class="remove-button" data-id="' . $row["id_nft"] . '"><i class="fas fa-xmark"></i></button></td>';
                            echo '</tr>';
                            $subtotal += $row["precio"];
                        }
                    }
                    echo '</table>';
                    echo '<div class="cart-total">';
                    echo '<p>Subtotal: ' . $subtotal . ' ETH</p>';
                    echo '</div>';
                    echo '<div class="checkout-button-container">';
                    echo '<a href="/" class="checkout-button">Continuar comprando</a>';
                    echo '<a href="/checkout" class="checkout-button">Proceder con la compra</a>';
                    echo '</div>';
                } else {
                    echo '<p>No hay productos en el carrito.</p>';
                }
            ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script>
        $(document).ready(function() {
            $('.remove-button').click(function() {
                var productId = $(this).data('id');
                $.ajax({
                    url: '/carrito',
                    method: 'POST',
                    data: { product_id: productId },
                    success: function(response) {
                        if (response.trim() !== 'No hay productos en el carrito.') {
                            location.reload();
                        } else {
                            $('.container').html('<p>No hay productos en el carrito.</p>');
                            $('table, .cart-total, .checkout-button-container').remove();
                        }
                    }
                });
            });
        });
    </script>
    <script src="script/script.js"></script>
</body>
</html>