<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mes = $_POST["Mes"];
    $year = $_POST["Year"];

    $meses = [
        "enero" => 1,
        "febrero" => 2,
        "marzo" => 3,
        "abril" => 4,
        "mayo" => 5,
        "junio" => 6,
        "julio" => 7,
        "agosto" => 8,
        "septiembre" => 9,
        "octubre" => 10,
        "noviembre" => 11,
        "diciembre" => 12
    ];

    $fechaActual = new DateTime();
    $fechaVencimiento = new DateTime($year . '-' . $meses[$mes] . '-01');

    if ($fechaVencimiento < $fechaActual) {
        echo "La tarjeta ha caducado";
    } else {
        echo "Pago realizado con éxito";
    }
}
include 'head.php';
    include 'header.php';
    ?>
    <main>
        <div class="container">
            <h2>Procesar pago</h2>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="script/script.js"></script>
</body>
</html>