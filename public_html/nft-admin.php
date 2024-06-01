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

$stmt = $conexion->prepare("SELECT id_coleccion, nombre_coleccion, creador FROM Coleccion_NFT");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $parametro === 'editar') {
    $id = $_POST['id'];
    $nombre_coleccion = $result->fetch_assoc()['nombre_coleccion'];

    $stmt_nft = $conexion->prepare("SELECT id_nft, nombre_nft, coleccion FROM NFT");
    $stmt_nft->execute();
    $result_nft = $stmt_nft->get_result();
    while ($row_nft = $result_nft->fetch_assoc()) {
        if ($row_nft['id_nft'] == $id) {
            $nombreOriginal = $row_nft['nombre_nft'];
            break;
        }
    }

    if ($nombreOriginal != $_POST['nombre']) {
        $oldDir = __DIR__ . '/img/colecciones/' . $nombre_coleccion . '/' . $nombreOriginal . '.png';
        $newDir = __DIR__ . '/img/colecciones/' . $nombre_coleccion . '/' . $_POST['nombre'] . '.png';
        rename($oldDir, $newDir);
    }

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $rutaDestino = __DIR__ . '/img/colecciones/' . $nombre_coleccion . '/' . $_POST['nombre'] . '.png';
    
        $directorio = dirname($rutaDestino);
        $oldmask = umask(0);
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }
        umask($oldmask);
        
        move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino);
    }

    $stmt = $conexion->prepare('UPDATE NFT SET nombre_nft = ?, precio = ? WHERE id_nft = ?');
    $stmt->bind_param('sdi', $_POST['nombre'], $_POST['precio'], $_POST['id']);
    $stmt->execute();

    header('Location: /nft-admin?nft=' . $row_nft['coleccion']);
    exit;
}

?>
<?php include __DIR__ . '/../includes/head.php'; ?>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main>
        <div class="container">
            <h2>Administración de NFTs</h2>
            <?php switch ($parametro) {
                case 'nueva':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $nombres = $_POST['nombre'];
                        $precios = $_POST['precio'];
                        $coleccion = $_POST['coleccion'];
                    
                        $stmt = $conexion->prepare('SELECT nombre_coleccion FROM Coleccion_NFT WHERE id_coleccion = ?');
                        $stmt->bind_param('i', $coleccion);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $nombre_coleccion = $result->fetch_assoc()['nombre_coleccion'];
                        $stmt->close();
                    
                        for ($i = 0; $i < count($nombres); $i++) {
                            $nombre = $nombres[$i];
                            $precio = $precios[$i];

                            if (isset($_FILES['foto']) && $_FILES['foto']['error'][$i] === UPLOAD_ERR_OK) {
                                $destino = __DIR__ . "/img/colecciones/$nombre_coleccion/";
                                if (!file_exists($destino)) {
                                    $oldmask = umask(0);
                                    if (!mkdir($destino, 0777, true)) {
                                        die('Error al crear el directorio.');
                                    }
                                    umask($oldmask);
                                }
                                $destino .= $nombre . '.png';
                                if (!move_uploaded_file($_FILES['foto']['tmp_name'][$i], $destino)) {
                                    die('Error al mover el archivo cargado.');
                                }
                            } else {
                                die('Error al cargar el archivo: ' . $_FILES['foto']['error'][$i]);
                            }

                            $stmt = $conexion->prepare('INSERT INTO NFT (nombre_nft, propietario_id, coleccion, precio, token_nft) VALUES (?, ?, ?, ?, UUID())');
                            $stmt->bind_param('siid', $nombre, $_SESSION['id_usuario'], $coleccion, $precio);
                            $stmt->execute();
                        }
                    
                        header('Location: /nft-admin?nft=' . $coleccion);
                        exit;
                    }
                    ?>
                    <form method="POST" class="login-form nueva-nft" id="nueva-nft" enctype="multipart/form-data">
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
                            <input type="file" id="foto" name="foto[]" accept="image/*" required>
                        </div>
                        <div class="contenedor-botones">
                            <button type="button" id="addNFT">+</button>
                            <button type="button" id="removeNFT">-</button>
                        </div>
                        <div class="contenedor-botones">
                            <button type="submit" class="editar-coleccion">Añadir NFT(s)</button>
                            <a href="/nft-admin" class="eliminar-coleccion">Cancelar</a>
                        </div>
                    </form>
                    <?php break;
                case 'editar':
                        $stmt_nft = $conexion->prepare("SELECT id_nft, nombre_nft, precio, coleccion FROM NFT WHERE id_nft = ?");
                        $stmt_nft->bind_param("i", $_GET['editar']);
                        $stmt_nft->execute();
                        $result_nft = $stmt_nft->get_result();
                        $row_nft = $result_nft->fetch_assoc();
                        ?>
                        <form method="POST" class="login-form" id="editar-nft">
                            <input type="hidden" name="id" value="<?= $row_nft['id_nft'] ?>">
                            
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre-<?= $row_nft['id_nft'] ?>" name="nombre" value="<?= $row_nft['nombre_nft'] ?>" required>
                            
                            <label for="precio">Precio (ETH)</label>
                            <input type="number" id="precio-<?= $row_nft['id_nft'] ?>" step="0.01" name="precio" value="<?= $row_nft['precio'] ?>" required>

                            <label for="imagen">Imagen</label>
                            <input type="file" name="imagen" accept="image/*">
                            
                            <div class="contenedor-botones">
                                <button type="submit" class="editar-coleccion">Guardar</button>
                                <a href="/nft-admin?nft=<?= $row_nft['coleccion'] ?>" class="eliminar-coleccion">Cancelar</a>
                            </div>
                        </form>
                        <?php
                        break;
                case 'nft':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['disponible'])) {
                        $id = $_POST['id'];
                        $disponible = $_POST['disponible'];
                    
                        $stmt = $conexion->prepare('UPDATE NFT SET disponible = ? WHERE id_nft = ?');
                        $stmt->bind_param('si', $disponible, $id);
                        $stmt->execute();
                        
                        if ($stmt->affected_rows > 0) {
                            http_response_code(200);
                        } else {
                            http_response_code(500);
                        }
                        exit;
                    }

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
                            $imagePath = 'img/colecciones/' . $row["nombre_coleccion"] . '/' . $row_nft["nombre_nft"] . '.png';
                            if (file_exists($imagePath)) {
                                echo '<img class="coleccion-img" src="' . $imagePath . '" alt="' . $row_nft["nombre_nft"] . '">';
                            }
                            echo '<h3>' . ucfirst($row_nft["nombre_nft"]) . '</h3>';
                            echo '<p>Precio: ' . $row_nft["precio"] . ' ETH</p>';
                            echo '<div style="display: flex; justify-content: space-between; align-items: center">';
                            echo '<p>Disponible: </p>';
                            echo '<label class="switch">';
                            $checked = $row_nft['disponible'] === 'Sí' ? 'checked' : '';
                            echo '<input type="checkbox" ' . $checked . ' class="disponibilidad" data-id="' . $row_nft['id_nft'] . '" data-coleccion="' . $row_nft['coleccion'] . '">';
                            echo '<span class="slider"</span>';
                            echo '</label>';
                            echo '</div>';
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
                                    $stmt_nft = $conexion->prepare("SELECT nombre_nft FROM NFT WHERE coleccion = ? GROUP BY id_nft ORDER BY id_nft ASC LIMIT 1");
                                    $stmt_nft->bind_param("i", $row["id_coleccion"]);
                                    $stmt_nft->execute();
                                    $result_nft = $stmt_nft->get_result();
        
                                    if ($result_nft && $result_nft->num_rows > 0) {
                                        $row_nft = $result_nft->fetch_assoc();
                                        $imagePath = 'img/colecciones/' . $row["nombre_coleccion"] . '/' . $row_nft["nombre_nft"] . '.png';
                                        if (file_exists($imagePath)) {
                                            echo '<img  class="coleccion-img" src="' . $imagePath . '" alt="' . $row["nombre_coleccion"] . '">';
                                        }
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
                            location.reload();
                        } else {
                            alert('Hubo un error al eliminar el NFT.');
                        }
                    } else {
                        alert('Hubo un error al eliminar el NFT.');
                    }
                };
            });
        });

        window.addEventListener('DOMContentLoaded', (event) => {
            const addNFTButton = document.getElementById('addNFT');
            if (addNFTButton) {
                addNFTButton.addEventListener('click', function() {
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
                        if (field === 'foto') {
                            input.setAttribute('accept', 'image/*');
                        }
                    
                        newDiv.appendChild(label);
                        newDiv.appendChild(input);
                    });
                
                    var buttonsDiv = document.querySelector('.contenedor-botones');
                    buttonsDiv.parentNode.insertBefore(newDiv, buttonsDiv);
                });
            }
        
            const removeNFTButton = document.getElementById('removeNFT');
            if (removeNFTButton) {
                removeNFTButton.addEventListener('click', function() {
                    var form = document.querySelector('.nueva-nft');
                    var divs = form.querySelectorAll('.nueva-nft-border');
                
                    if (divs.length > 1) {
                        form.removeChild(divs[divs.length - 1]);
                    }
                });
            }

            const disponibilidadButton = document.querySelectorAll('.disponibilidad');
            if (disponibilidadButton) {
                disponibilidadButton.forEach(function(switchEl) {
                    switchEl.addEventListener('change', function() {
                        var id = this.dataset.id;
                        var coleccion = this.dataset.coleccion;
                        var disponible = this.checked ? 'Sí' : 'No';
                        var formData = new FormData();
                        formData.append('disponible', disponible);
                        formData.append('id', id);
                        fetch(`/nft-admin?nft=${coleccion}`, {
                            method: 'POST',
                            body: formData
                        }).then(function(response) {
                            if (!response.ok) {
                                alert('Hubo un error al actualizar la disponibilidad.');
                            }
                        });
                    });
                });
            }
        });
    </script>
</body>
</html>