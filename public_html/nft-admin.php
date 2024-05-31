<?php
$title = 'NFTs - Administrador';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = $_POST['delete'];

    $stmt = $conexion->prepare('DELETE FROM NFT WHERE id_nft = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Hubo un error al eliminar el NFT.']);
    }
    exit;
}

?>
<?php include __DIR__ . '/../includes/head.php'; ?>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main>
        <div class="container">
            <h2>Administración de NFTs</h2>
            <?php switch ($parametro) {
                case 'nueva': ?>
                    <form class="login-form nueva-nft">
                        <label for="coleccion">Colección:</label>
                        <select id="coleccion" name="coleccion">
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <option value="<?= $row['id_coleccion'] ?>"><?= $row['nombre_coleccion'] ?></option>
                            <?php } ?>
                        </select>
                        <div class="nueva-nft-border">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre[]" required>
                            <label for="precio">Precio (ETH):</label>
                            <input type="number" id="precio" name="precio[]" step="0.01" required>
                            <label for="foto">Foto:</label>
                            <input type="file" id="foto" name="foto[]" required>
                        </div>
                        <div class="contenedor-botones">
                            <button type="button" id="addNFT">+</button>
                            <button type="button" id="removeNFT">-</button>
                        </div>
                        <input type="submit" value="Añadir NFT">
                    </form>
                    <?php break;
                case 'nft':
                    if (isset($_GET['nft'])) {
                        $id_coleccion = $_GET['nft'];
                    }

                    $query = "SELECT * FROM Coleccion_NFT WHERE id_coleccion = ?";
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param('i', $id_coleccion);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    $query_nft = "SELECT * FROM NFT WHERE coleccion = ?";
                    $stmt_nft = $conexion->prepare($query_nft);
                    $stmt_nft->bind_param('i', $_GET['nft']);
                    $stmt_nft->execute();
                    $result_nft = $stmt_nft->get_result();
                    
                    if ($result_nft->num_rows > 0) {
                        echo '<div class="nft-grid">';
                        while ($row_nft = $result_nft->fetch_assoc()) {
                            echo '<div class="nft-box">';
                            echo '<img src="img/colecciones/' . $row["nombre_coleccion"] . '/' . $row_nft["nombre_nft"] . '.png" alt="' . $row_nft["nombre_nft"] . '">';
                            echo '<h3>' . ucfirst($row_nft["nombre_nft"]) . '</h3>';
                            echo '<p>Precio: ' . $row_nft["precio"] . ' ETH</p>';
                            echo '<div class="contenedor-botones">';
                            echo '<a href="/nft-admin?editar=' . $row_nft['id_nft'] . '" class="editar-coleccion">Editar</a>';
                            echo '<a href="" class="eliminar-coleccion" id="eliminar-nft" data-id="' . $row_nft['id_nft'] . '">Eliminar</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No se han encontrado NFT en esta colección.</p>';
                    }
                    break;
                default:
                ?>
                    <div class="boton-coleccion">
                    <a href="/nft-admin?nueva" class="nueva-coleccion">Nueva NFT</a>
                    </div>
                    <div class="colecciones">
                    <?php if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <a href="/nft-admin?nft=<?= $row['id_coleccion'] ?>">
                                <div class="coleccion-nft">
                                    <?php
                                    $imagePath = 'img/colecciones/' . $row["nombre_coleccion"] . '/' . $row["nombre_coleccion"] . '1.png';
                                    if (file_exists($imagePath)) {
                                        echo '<img class="coleccion-img" src="' . $imagePath . '" alt="' . $row["nombre_coleccion"] . '">';
                                    }
                                    ?>
                                    <p class="coleccion-nombre"><?= ucfirst($row['nombre_coleccion']) ?></p>
                                </div>
                            </a>
                        <?php
                        }
                    } else {
                        echo "<p>No se encontraron colecciones disponibles.</p>";
                    }
                    break;
            }
            ?>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="script/script.js"></script>
    <script>
        var deleteLinks = document.querySelectorAll('#eliminar-nft');
        
        deleteLinks.forEach(function(deleteLink) {
            deleteLink.addEventListener('click', function(e) {
                e.preventDefault();
        
                if (!confirm('¿Estás seguro/a de que quieres eliminar este NFT?')) {
                    return;
                }
        
                var id = this.getAttribute('data-id');
        
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/nft-admin', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
                var params = 'delete=' + encodeURIComponent(id);
                xhr.send(params);
        
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            location.href = '/nft-admin';
                        } else {
                            alert('Hubo un error al eliminar el NFT.');
                        }
                    } else {
                        alert('Hubo un error al eliminar el NFT.');
                    }
                };
            });
        });

        document.getElementById('addNFT').addEventListener('click', function() {
            var form = document.querySelector('.nueva-nft');
        
            var newDiv = document.createElement('div');
            newDiv.className = 'nueva-nft-border';
            newDiv.style.marginTop = '2em';
        
            ['nombre', 'precio', 'foto'].forEach(function(field) {
                var label = document.createElement('label');
                label.setAttribute('for', field);
                label.textContent = field === 'precio' ? 'Precio (ETH):' : field.charAt(0).toUpperCase() + field.slice(1) + ':';
        
                var input = document.createElement('input');
                input.setAttribute('type', field === 'foto' ? 'file' : (field === 'precio' ? 'number' : 'text'));
                input.setAttribute('id', field);
                input.setAttribute('name', field + '[]');
                input.required = true;
                if (field === 'precio') {
                    input.setAttribute('step', '0.01');
                }
        
                newDiv.appendChild(label);
                newDiv.appendChild(input);
            });
        
            var buttonsDiv = document.querySelector('.contenedor-botones');
            buttonsDiv.parentNode.insertBefore(newDiv, buttonsDiv);
        });
        
        document.getElementById('removeNFT').addEventListener('click', function() {
            var form = document.querySelector('.nueva-nft');
            var divs = form.querySelectorAll('.nueva-nft-border');
        
            if (divs.length > 1) {
                form.removeChild(divs[divs.length - 1]);
            }
        });
    </script>
</body>
</html>