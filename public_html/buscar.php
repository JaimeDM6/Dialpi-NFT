<?php
    $title = 'Búsqueda';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/functions.php';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main>
        <div class="container">
            <h2>Búsqueda</h2>
            <?php
                $termino_busqueda = '%' . $_GET['q'] . '%';
            
                $query = "SELECT * FROM Coleccion_NFT WHERE nombre_coleccion LIKE ? OR creador LIKE ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('ss', $termino_busqueda, $termino_busqueda);
                $stmt->execute();
                $result = $stmt->get_result();
            
                if ($result->num_rows > 0) {
                    echo '<div class="collection-grid">';
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="collection-box">';
                        echo '<a href="/coleccion?id=' . $row["id_coleccion"] . '">';
                        echo '<img src="img/colecciones/' . $row["nombre_coleccion"] . '/' . $row["nombre_coleccion"] . '1.png" alt="' . $row["nombre_coleccion"] . '">';
                        echo '<h3>' . ucfirst($row["nombre_coleccion"]) . '</h3>';
                        echo '<p>Creador: ' . $row["creador"] . '</p>';
                        $stmt_sum = $conexion->prepare("SELECT IFNULL(SUM(precio), 0) as total_precio FROM NFT WHERE coleccion = ?");
                        $stmt_sum->bind_param("i", $row["id_coleccion"]);
                        $stmt_sum->execute();
                        $result_sum = $stmt_sum->get_result();
                        
                        if ($result_sum && $result_sum->num_rows > 0) {
                            $row_sum = $result_sum->fetch_assoc();
                            echo '<p style="margin-bottom: 10px;">Precio: ' . $row_sum["total_precio"] . ' ETH</p>';
                        }
                        echo '</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>No se han encontrado colecciones con el nombre "' . $_GET['q'] . '".</p>';
                }
            ?>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>