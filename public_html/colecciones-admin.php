<?php
$title = 'Colecciones - Administrador';
session_start();
ob_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['administrador']) || $_SESSION['administrador'] !== true) {
    http_response_code(403);
    include('403.php');
    exit();
}

$stmt = $conexion->prepare('SELECT * FROM Coleccion_NFT');
$stmt->execute();
$result = $stmt->get_result();
$colecciones = $result->fetch_all(MYSQLI_ASSOC);

$query_nft = "SELECT * FROM NFT WHERE coleccion = ?";
$stmt_nft = $conexion->prepare($query_nft);
$stmt_nft->bind_param('i', $id_coleccion);
$stmt_nft->execute();
$result_nft = $stmt_nft->get_result();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    
    $stmt = $conexion->prepare('INSERT INTO Coleccion_NFT (nombre_coleccion, creador, precio_coleccion) VALUES (?, ?, ?)');
    $stmt->bind_param('ssd', $_POST['nombre'], $_POST['creador'], $_POST['precio']);
    $stmt->execute();
    
    header('Location: /colecciones-admin');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete') {
        $id = $_POST['id'];

        $stmt = $conexion->prepare('DELETE FROM Coleccion_NFT WHERE id_coleccion = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Hubo un error al eliminar la colección.']);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    
    $stmt = $conexion->prepare('UPDATE Coleccion_NFT SET nombre_coleccion = ?, creador = ?, precio_coleccion = ? WHERE id_coleccion = ?');
    $stmt->bind_param('ssdi', $_POST['nombre'], $_POST['creador'], $_POST['precio'], $_POST['id']);
    $stmt->execute();
    
    header('Location: /colecciones-admin');
    exit;
}

?>
<?php include __DIR__ . '/../includes/head.php'; ?>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main>
        <div class="container">
            <h1>Administración de Colecciones</h1>
            <div class="boton-coleccion">
                <button class="nueva-coleccion" onclick="nuevaColeccion()">Nueva Colección</button>
            </div>

            <form method="POST" class="login-form agregar-coleccion" id="nueva-coleccion" style="display: none;">
                <h2>Agregar nueva colección</h2>
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="creador" placeholder="Creador" required>
                <input type="number" step="0.01" name="precio" placeholder="Precio (ETH)" required>
                <div class="contenedor-botones">
                    <button type="submit" name="agregar" class="editar-coleccion">Agregar</button>
                    <button type="button" class="eliminar-coleccion" onclick="cancelarNueva()">Cancelar</button>
                </div>
            </form>

            <div class="collection-grid" id="colecciones">
                <?php foreach ($colecciones as $coleccion): ?>
                    <?php
                    $stmt_nft = $conexion->prepare('SELECT nombre_nft FROM NFT WHERE coleccion = ? LIMIT 1');
                    $stmt_nft->bind_param('i', $coleccion['id_coleccion']);
                    $stmt_nft->execute();
                    $result_nft = $stmt_nft->get_result();
                    $nft = $result_nft->fetch_assoc();
                    $imagen = $nft['nombre_nft'] . '.png';
                    ?>
                    <div class="collection-box" id="coleccion-<?= $coleccion['id_coleccion'] ?>">
                        <img src="img/colecciones/<?= $coleccion['nombre_coleccion'] ?>/<?= $imagen ?>" alt="<?= $coleccion['nombre_coleccion'] ?>">
                        <h3><?= ucfirst($coleccion['nombre_coleccion']) ?></h3>
                        <p>Creador: <?= $coleccion['creador'] ?></p>
                        <p style="margin-bottom: 10px;">Precio: <?= $coleccion['precio_coleccion'] ?> ETH</p>

                        <div class="contenedor-botones">
                            <button class="editar-coleccion" onclick="editar('<?= $coleccion['id_coleccion'] ?>')">Editar</button>
                            <button class="eliminar-coleccion" onclick="eliminar('<?= $coleccion['id_coleccion'] ?>')">Eliminar</button>
                        </div>
                       
                    </div>
                <?php endforeach; ?>
            </div>
            <?php foreach ($colecciones as $coleccion): ?>
                <form method="POST" class="login-form" id="editar-<?= $coleccion['id_coleccion'] ?>" style="display: none;">
                    <input type="hidden" name="id" value="<?= $coleccion['id_coleccion'] ?>">
                    
                    <label for="nombre-<?= $coleccion['id_coleccion'] ?>">Nombre</label>
                    <input type="text" id="nombre-<?= $coleccion['id_coleccion'] ?>" name="nombre" value="<?= $coleccion['nombre_coleccion'] ?>" required>
                    
                    <label for="creador-<?= $coleccion['id_coleccion'] ?>">Creador</label>
                    <input type="text" id="creador-<?= $coleccion['id_coleccion'] ?>" name="creador" value="<?= $coleccion['creador'] ?>" required>
                    
                    <label for="precio-<?= $coleccion['id_coleccion'] ?>">Precio (ETH)</label>
                    <input type="number" id="precio-<?= $coleccion['id_coleccion'] ?>" step="0.01" name="precio" value="<?= $coleccion['precio_coleccion'] ?>" required>
                    
                    <div class="contenedor-botones">
                        <button type="submit" class="editar-coleccion" name="editar">Guardar</button>
                        <button type="button" class="eliminar-coleccion" onclick="cancelar('<?= $coleccion['id_coleccion'] ?>')">Cancelar</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script>
        function nuevaColeccion() {
            document.getElementById('nueva-coleccion').style.display = 'block';
            document.getElementById('colecciones').style.display = 'none';
        }

        function cancelarNueva() {
            document.getElementById('nueva-coleccion').style.display = 'none';
            document.getElementById('colecciones').style.display = 'grid';
        }

        function editar(id) {
            var colecciones = document.getElementsByClassName('collection-box');
            for (var i = 0; i < colecciones.length; i++) {
                colecciones[i].style.display = 'none';
            }
            document.getElementById('editar-' + id).style.display = 'block';
        }
        
        function cancelar(id) {
            var colecciones = document.getElementsByClassName('collection-box');
            for (var i = 0; i < colecciones.length; i++) {
                colecciones[i].style.display = 'block';
            }
            document.getElementById('editar-' + id).style.display = 'none';
        }

        function eliminar(id) {
            if (confirm('¿Estás seguro/a de que quieres eliminar esta colección?')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/colecciones-admin', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('action=delete&id=' + id);
        
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('coleccion-' + id).remove();
                        } else {
                            alert(response.error);
                        }
                    } else {
                        alert('Hubo un error al eliminar la colección.');
                    }
                };
            }
        }
    </script>
    <script src="script/script.js"></script>
</body>
</html>