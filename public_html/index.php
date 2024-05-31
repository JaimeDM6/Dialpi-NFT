<?php
$title = 'Dialpi NFT';
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['pago_realizado'])) {
    unset($_SESSION['cart']);
    unset($_SESSION['numero_factura']);
    unset($_SESSION['pago_realizado']);
    unset($_SESSION['pago_confirmado']);

    if (isset($_SESSION['invitado'])) {
        unset($_SESSION['invitado']);
    }
    
    exit;
}

include __DIR__ . '/../includes/head.php';
    include __DIR__ . '/../includes/header.php';
    ?>
    <main>
        <div class="container">
            <h2>Colecciones Destacadas</h2>
            <?php
            $stmt = $conexion->prepare("SELECT id_coleccion, nombre_coleccion, creador, disponible, precio_coleccion FROM Coleccion_NFT");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo '<div class="collection-grid">';
                while ($row = $result->fetch_assoc()) {
                    if ($row['disponible'] === 'Sí') {
                        echo '<div class="collection-box">';
                        echo '<a href="/coleccion?id=' . $row["id_coleccion"] . '">';

                        $imagePath = 'img/colecciones/' . $row["nombre_coleccion"] . '/' . $row["nombre_coleccion"] . '1.png';
                        if (file_exists($imagePath)) {
                            echo '<img src="' . $imagePath . '" alt="' . $row["nombre_coleccion"] . '">';
                        }

                        echo '<h3>' . ucfirst($row["nombre_coleccion"]) . '</h3>';
                        echo '<p>Creador: ' . $row["creador"] . '</p>';
                        echo '<p style="margin-bottom: 10px;">Precio: ' . $row["precio_coleccion"] . ' ETH</p>';
                        echo '</a>';
                        echo '</div>';
                    } else {
                        echo '<div class="collection-box-disabled" style="opacity: 0.5; cursor: not-allowed;">';
                        echo '<a>';

                        $imagePath = 'img/colecciones/' . $row["nombre_coleccion"] . '/' . $row["nombre_coleccion"] . '1.png';
                        if (file_exists($imagePath)) {
                            echo '<img src="' . $imagePath . '" alt="' . $row["nombre_coleccion"] . '">';
                        }

                        echo '<h3>' . ucfirst($row["nombre_coleccion"]) . '</h3>';
                        echo '<p>Creador: ' . $row["creador"] . '</p>';
                        echo '<p style="margin-bottom: 10px;">Precio: ' . $row["precio_coleccion"] . ' ETH</p>';
                        echo '</a>';
                        echo '</div>';
                    }
                }
                echo '</div>';
            } else {
                echo "<p>No se encontraron colecciones disponibles.</p>";
            }
            ?>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="script/script.js"></script>
</body>
</html>