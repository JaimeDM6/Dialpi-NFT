<?php
$title = 'Procesar pago';
session_start();

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
        $_SESSION['pago_realizado'] = true;
    }
}

include 'head.php';

if (isset($_SESSION['pago_realizado'])) {
    echo "<div style='display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column;'>";
    echo "<h1>Procesando pago...</h1>";
    echo "<img src='img/procesando.gif' alt='Procesando...' height=100>";
    echo "</div>";

    echo "<script>
        setTimeout(function() {
            location.href = '/procesar-pago?confirmar';
        }, 5000);
    </script>";
    unset($_SESSION['pago_realizado']);
}

if (isset($_GET['confirmar'])) {
    include 'header-center.php';
    ?>
    <main>
        <div class="container">
            <h2>El pago se ha realizado correctamente.</h2>
            <a href="/certificado">Pulse aquí para descargar su certificado.</a>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="script/script.js"></script>
</body>
</html>
<?php
}
?>