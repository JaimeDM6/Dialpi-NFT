<?php include 'head.php'; ?>
    <?php
        session_start();
        require_once 'conexion.php';
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    ?>
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
            <h2>Colecciones Destacadas</h2>
            <?php
            $query = "SELECT id_coleccion, nombre_coleccion, creador, precio_coleccion FROM Coleccion_NFT WHERE disponible = 'Sí'";
            $result = $conexion->query($query);
            
            if ($result->num_rows > 0) {
                echo '<div class="collection-grid">';
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="collection-box">';
                    echo '<a href="/coleccion?id=' . $row["id_coleccion"] . '">';
                    echo '<img src="img/colecciones/' . $row["nombre_coleccion"] . '/' . $row["nombre_coleccion"] . '1.png" alt="' . $row["nombre_coleccion"] . '">';
                    echo '<h3>' . ucfirst($row["nombre_coleccion"]) . '</h3>';
                    echo '<p>Creador: ' . $row["creador"] . '</p>';
                    echo '<p style="margin-bottom: 10px;">Precio: ' . $row["precio_coleccion"] . ' ETH</p>';
                    echo '</a>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo "<p>No se encontraron colecciones disponibles.</p>";
            }
            ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="script/script.js"></script>
</body>
</html>