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

$parametro = array_key_first($_GET);

$stmt = $conexion->prepare("SELECT id_coleccion, nombre_coleccion, creador, precio_coleccion FROM Coleccion_NFT");
$stmt->execute();
$result = $stmt->get_result();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $parametro === 'nueva') {
    
    $stmt = $conexion->prepare('INSERT INTO Coleccion_NFT (nombre_coleccion, creador, precio_coleccion) VALUES (?, ?, ?)');
    $stmt->bind_param('ssd', $_POST['nombre'], $_POST['creador'], $_POST['precio']);
    $stmt->execute();
    
    header('Location: /colecciones-admin');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = $_POST['delete'];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $parametro === 'editar') {
    $id = $_POST['id'];
    while ($row = $result->fetch_assoc()) {
        if ($row['id_coleccion'] == $id) {
            $nombreOriginal = $row['nombre_coleccion'];
            break;
        }
    }

    $stmt = $conexion->prepare('UPDATE Coleccion_NFT SET nombre_coleccion = ?, creador = ?, precio_coleccion = ? WHERE id_coleccion = ?');
    $stmt->bind_param('ssdi', $_POST['nombre'], $_POST['creador'], $_POST['precio'], $_POST['id']);
    $stmt->execute();

    if ($nombreOriginal != $_POST['nombre']) {
        $oldDir = __DIR__ . '/img/colecciones/' . $nombreOriginal;
        $newDir = __DIR__ . '/img/colecciones/' . $_POST['nombre'];
        rename($oldDir, $newDir);
    }
    
    header('Location: /colecciones-admin');
    exit;
}

?>
<?php include __DIR__ . '/../includes/head.php'; ?>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main>
        <div class="container">
            <h2>Administración de Colecciones</h2>
            <?php
            switch ($parametro) {
                case 'nueva': ?>
                    <form method="POST" class="login-form agregar-coleccion" id="nueva-coleccion">
                        <h2>Agregar nueva colección</h2>
                        <input type="text" name="nombre" placeholder="Nombre" required>
                        <input type="text" name="creador" placeholder="Creador" required>
                        <input type="number" step="0.01" name="precio" placeholder="Precio (ETH)" required>
                        <div class="contenedor-botones">
                            <button type="submit" class="editar-coleccion">Agregar</button>
                            <a href="/colecciones-admin" class="eliminar-coleccion">Cancelar</a>
                        </div>
                    </form>
                    <?php
                    break;
                case 'editar':
                    $stmt = $conexion->prepare("SELECT id_coleccion, nombre_coleccion, creador, disponible, precio_coleccion FROM Coleccion_NFT WHERE id_coleccion = ?");
                    $stmt->bind_param("i", $_GET['editar']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    ?>
                    <form method="POST" class="login-form" id="editar-coleccion">
                        <input type="hidden" name="id" value="<?= $row['id_coleccion'] ?>">
                        
                        <label for="nombre-<?= $row['id_coleccion'] ?>">Nombre</label>
                        <input type="text" id="nombre-<?= $row['id_coleccion'] ?>" name="nombre" value="<?= $row['nombre_coleccion'] ?>" required>
                        
                        <label for="creador-<?= $row['id_coleccion'] ?>">Creador</label>
                        <input type="text" id="creador-<?= $row['id_coleccion'] ?>" name="creador" value="<?= $row['creador'] ?>" required>
                        
                        <label for="precio-<?= $row['id_coleccion'] ?>">Precio (ETH)</label>
                        <input type="number" id="precio-<?= $row['id_coleccion'] ?>" step="0.01" name="precio" value="<?= $row['precio_coleccion'] ?>" required>
                        
                        <div class="contenedor-botones">
                            <button type="submit" class="editar-coleccion">Guardar</button>
                            <a href="/colecciones-admin" class="eliminar-coleccion">Cancelar</a>
                        </div>
                    </form>
                    <?php
                    break;
                default:
                    ?>
                    <div class="boton-coleccion">
                        <a href="/colecciones-admin?nueva" class="nueva-coleccion">Nueva Colección</a>
                    </div>
    
                    <?php    
                    if ($result->num_rows > 0) {
                        echo '<div class="collection-grid">';
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="collection-box">';
                            
                            $imagePath = 'img/colecciones/' . $row["nombre_coleccion"] . '/' . $row["nombre_coleccion"] . '1.png';
                            if (file_exists($imagePath)) {
                                echo '<img src="' . $imagePath . '" alt="' . $row["nombre_coleccion"] . '">';
                            }
                            
                            echo '<h3>' . ucfirst($row["nombre_coleccion"]) . '</h3>';
                            echo '<p>Creador: ' . $row["creador"] . '</p>';
                            echo '<p style="margin-bottom: 10px;">Precio: ' . $row["precio_coleccion"] . ' ETH</p>';
                            echo '<div class="contenedor-botones">';
                            echo '<a href="/colecciones-admin?editar=' . $row['id_coleccion'] . '" class="editar-coleccion">Editar</a>';
                            echo '<a href="" class="eliminar-coleccion" id="eliminar-coleccion" data-id="' . $row['id_coleccion'] . '">Eliminar</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo "<p>No se encontraron colecciones disponibles.</p>";
                    }
                    break;
            } ?>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="script/script.js"></script>
    <script>
        var deleteLinks = document.querySelectorAll('#eliminar-coleccion');
        
        deleteLinks.forEach(function(deleteLink) {
            deleteLink.addEventListener('click', function(e) {
                e.preventDefault();
        
                if (!confirm('¿Estás seguro/a de que quieres eliminar esta colección?')) {
                    return;
                }
        
                var id = this.getAttribute('data-id');
        
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/colecciones-admin', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
                var params = 'delete=' + encodeURIComponent(id);
                xhr.send(params);
        
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            location.href = '/colecciones-admin';
                        } else {
                            alert('Hubo un error al eliminar la colección.');
                        }
                    } else {
                        alert('Hubo un error al eliminar la colección.');
                    }
                };
            });
        });
    </script>
</body>
</html>